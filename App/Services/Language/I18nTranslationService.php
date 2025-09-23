<?php

declare(strict_types=1);

namespace App\Services\Language;

use App\Contracts\Translation\TranslationService as TranslationServiceContract;
use App\Repositories\I18nStringsRepository;       // kept for compatibility (not strictly required)
use App\Repositories\I18nTranslationsRepository;
use App\Repositories\I18nClientsRepository;
use App\Repositories\I18nResourcesRepository;
use App\Repositories\LanguageRepository;
use App\Services\Database\DatabaseService;
use App\Services\LoggerService as Log;
use App\Support\Async;
use App\Configuration\Config;

class I18nTranslationService implements TranslationServiceContract
{
    public function __construct(
        private I18nStringsRepository      $strings,      // not required by this implementation but left for DI BC
        private I18nTranslationsRepository $translations,
        private I18nClientsRepository      $clients,
        private I18nResourcesRepository    $resources,
        private DatabaseService            $db,
        private LanguageRepository         $languages,
        private string                     $baseLanguage = 'eng00'
    ) {}

    public function baseLanguage(): string
    {
        return $this->baseLanguage;
    }

    /**
     * Google-only translation flow.
     * Ensures masters exist in i18n_strings, then translates/queues.
     */
    public function translateBundle(
        array $bundle,
        string $languageCodeHL,
        ?string $variant,
        array $ctx = []
    ): array {
        // ---- context --------------------------------------------------------
        $type            = (string)($ctx['kind']            ?? 'interface');
        $resourceSubject = (string)($ctx['resourceSubject'] ?? 'app');
        $resourceVariant = (string)($ctx['resourceVariant'] ?? ($variant ?: 'default'));
        $clientCode      = (string)($ctx['clientCode']      ?? 'wsu');
        $isBase          = (bool)  ($ctx['isBase']          ?? ($languageCodeHL === $this->baseLanguage));
        $normVariant     = (string)($ctx['variant']         ?? ($variant ?: 'default'));
        $dbg             = (bool)  ($ctx['debug']           ?? Config::get('logging.i18n_debug', false));

        $clientId = $this->clients->getIdByCode($clientCode);
        if (!$clientId) {
            throw new \RuntimeException("Unknown clientCode '{$clientCode}'");
        }
        $resourceId = $this->resources->getIdByTypeSubjectVariant($type, $resourceSubject, $resourceVariant);
        if (!$resourceId) {
            throw new \RuntimeException("Unknown resource {$type}/{$resourceSubject}/{$resourceVariant}");
        }

        if ($dbg) {
            Log::logDebug('I18nTr-060', 'ctxids', [
                'isBase' => $isBase,
                'kind' => $type, 'subject' => $resourceSubject, 'variant' => $resourceVariant,
                'clientCode' => $clientCode, 'clientId' => $clientId, 'resourceId' => $resourceId,
                'languageCodeHL' => $languageCodeHL,
            ]);
        }

        // ---- extract masters (includes "Next Video") ------------------------
        if ($dbg) { Log::logDebug('I18nTr-076', 'bundle (pre-extract)', $bundle); }

        $masters = $this->extractMasterTexts($bundle); // [['key'=>'a.b.c','text'=>'...'], ...]
        if ($dbg) { Log::logDebug('I18nTr-077', 'masters (raw)', $masters); }

        // ---- ensure masters exist in i18n_strings, then build map+ids -------
        [$stringMap, $stringIds] = $this->ensureMastersAndMap($clientId, $resourceId, $masters, $dbg);
        if ($dbg) {
            Log::logDebug('I18nTr-101', 'stringMap.keys.sample', array_slice(array_keys($stringMap), 0, 10));
            Log::logDebug('I18nTr-102', 'stringIds.sample', array_slice($stringIds, 0, 10));
        }

        // Base language short-circuit: keep English, but we keep the catalog in sync.
        if ($isBase) {
            return $this->withMeta($bundle, [
                'resourceSubject'      => $resourceSubject,
                'resourceVariant'      => $resourceVariant,
                'clientCode'           => $clientCode,
                'languageCodeHL'       => $languageCodeHL,
                'languageCodeGoogle'   => 'en',
                'variant'              => $normVariant,
                'keysTotal'            => count($stringIds),
                'keysMissing'          => 0,
                'keysFuzzy'            => $bundle['meta']['keysFuzzy'] ?? 0,
                'translationComplete'  => true,
            ]);
        }

        // ---- resolve Google code from HL -----------------------------------
        $google = $this->languages->getCodeGoogleFromCodeHL($languageCodeHL) ?? '';
        if ($google === '') { $google = strtolower(substr($languageCodeHL, 0, 2)); }

        if (empty($stringIds)) {
            return $this->withMeta($bundle, [
                'resourceSubject'      => $resourceSubject,
                'resourceVariant'      => $resourceVariant,
                'clientCode'           => $clientCode,
                'languageCodeHL'       => $languageCodeHL,
                'languageCodeGoogle'   => $google,
                'variant'              => $normVariant,
                'keysTotal'            => 0,
                'keysMissing'          => 0,
                'keysFuzzy'            => $bundle['meta']['keysFuzzy'] ?? 0,
                'translationComplete'  => true,
            ]);
        }

        if ($dbg) { Log::logDebug('I18nTr-121', 'stringIds', $stringIds); }

        // ---- fetch translations (Google-only) -------------------------------
        $rowsGoogle = $this->translations->fetchByStringIdsAndLanguageGoogle($stringIds, $google);
        if ($dbg) { Log::logDebug('I18nTr-126', 'rowsGoogle', $rowsGoogle); }

        $trById = [];
        foreach ($rowsGoogle as $r) {
            $sid = (int)($r['stringId'] ?? 0);
            if ($sid > 0) { $trById[$sid] = (string)($r['translatedText'] ?? ''); }
        }

        // ---- count translated vs missing; enqueue missing -------------------
        $keysTotal     = count($stringIds);
        $translatedCnt = 0;
        $missingRows   = [];

        $norm = static function (?string $s): string {
            if ($s === null) return '';
            $s = preg_replace('/\s+/u', ' ', trim($s));
            return \function_exists('mb_strtolower') ? mb_strtolower($s, 'UTF-8') : strtolower($s);
        };

        foreach ($masters as $m) {
            $dot = (string)($m['key']  ?? '');
            $txt = (string)($m['text'] ?? '');
            if ($txt === '') { continue; }

            $shaHex = sha1($txt);
            $shaKey = 'sha1:' . $shaHex;

            $sid = $stringMap[$dot] ?? $stringMap[$shaKey] ?? $stringMap[$shaHex] ?? null;
            $sid = ($sid !== null) ? (int)$sid : null;

            $applied = ($sid !== null && isset($trById[$sid])) ? (string)$trById[$sid] : '';
            $isTranslated = ($applied !== '') && ($norm($applied) !== $norm($txt));

            if ($isTranslated) {
                $translatedCnt++;
            } else {
                // missing or English fallback → enqueue
                $missingRows[] = [
                    'stringKey' => ($dot !== '' ? $dot : $shaKey),
                    'keyHash'   => $shaHex,
                    'sid'       => $sid,
                    'text'      => $txt,
                ];
            }
        }
        foreach ($masters as $m) {
            if (($m['key'] ?? '') === 'interface.nextVideo') {
                Log::logDebug('probe.master.nextVideo', [
                    'present' => true,
                    'text'    => $m['text'],
                    'sha1'    => sha1((string)$m['text']),
                ]);
            }
        }

        $keysMissing = max(0, $keysTotal - $translatedCnt);

        if (!empty($missingRows)) {
            foreach ($missingRows as $mr) {
                $this->enqueueMissing(
                    clientCode:            $clientCode,
                    resourceType:          $type,
                    subject:               $resourceSubject,
                    variant:               $resourceVariant,
                    stringKey:             $mr['stringKey'],
                    sourceKeyHash:         $mr['keyHash'],
                    sourceStringId:        $mr['sid'],
                    sourceLanguageGoogle:  'en',
                    targetLanguageGoogle:  $google,
                    sourceText:            $mr['text'],
                    priority:              0
                );
            }

            // optional: kick a one-shot worker
            Async::php(__DIR__ . '/../../../bin/translate-queue.php', [
                '--once',
                '--lang=' . $languageCodeHL,
                '--client=' . $clientCode,
                '--type=' . $type,
                '--subject=' . $resourceSubject,
                '--variant=' . $resourceVariant,
            ]);
        }

        // ---- apply translations back onto the bundle ------------------------
        $out = $this->applyTranslationsByStringId($bundle, $stringMap, $trById);

        // ---- meta -----------------------------------------------------------
        $out = $this->withMeta($out, [
            'resourceSubject'      => $resourceSubject,
            'resourceVariant'      => $resourceVariant,
            'clientCode'           => $clientCode,
            'languageCodeHL'       => $languageCodeHL,
            'languageCodeGoogle'   => $google,
            'variant'              => $normVariant,
            'keysTotal'            => $keysTotal,
            'keysMissing'          => $keysMissing,
            'keysFuzzy'            => $out['meta']['keysFuzzy'] ?? 0,
            'translationComplete'  => ($keysMissing === 0),
            'fallbackCount'        => $keysMissing,
        ]);

        return $out;
    }

    // ---------------------------------------------------------------------
    // internals (ordering & neatness)
    // ---------------------------------------------------------------------

    /**
 * Ensure every master line exists in i18n_strings (by clientId, resourceId, keyHash),
 * then return [$stringMap, $stringIds].
 *
 * $stringMap is keyed by:
 *   - dot key (e.g. "interface.nextVideo")
 *   - "sha1:<hex>"
 *   - "<hex>"
 */
    private function ensureMastersAndMap(
        int $clientId,
        int $resourceId,
        array $masters,
        bool $dbg = false
    ): array {
        // 1) Build unique set of (keyHash => englishText) from the bundle
        $hashToText = [];
        foreach ($masters as $m) {
            $txt = (string)($m['text'] ?? '');
            if ($txt === '') { continue; }
            $hashToText[sha1($txt)] = $txt;
        }
        if (!$hashToText) {
            return [[], []];
        }

        // 2) Read existing rows for these hashes (scope by clientId/resourceId)
        [$in, $params] = $this->buildInParams(array_keys($hashToText), 'h');
        $sel = $this->db->prepare(
            "SELECT stringId, keyHash, englishText
            FROM i18n_strings
            WHERE clientId = :c AND resourceId = :r AND keyHash IN ($in)"
        );
        $sel->execute([':c' => $clientId, ':r' => $resourceId] + $params);

        $haveId   = []; // keyHash => stringId
        $haveText = []; // keyHash => englishText
        while ($row = $sel->fetch(\PDO::FETCH_ASSOC)) {
            $kh = (string)$row['keyHash'];
            $haveId[$kh]   = (int)$row['stringId'];
            $haveText[$kh] = (string)$row['englishText'];
        }

        // 3) Insert truly missing rows (no ON DUPLICATE to avoid burning AUTO_INCREMENT)
        $ins = $this->db->prepare(
            "INSERT INTO i18n_strings
                (clientId, resourceId, keyHash, englishText, createdAt, updatedAt)
            SELECT :c, :r, :h, :t, NOW(), NOW() FROM DUAL
            WHERE NOT EXISTS (
                SELECT 1
                FROM i18n_strings
                WHERE clientId = :c AND resourceId = :r AND keyHash = :h
            )"
        );
        foreach ($hashToText as $h => $t) {
            if (isset($haveId[$h])) { continue; }
            try {
                $ins->execute([':c' => $clientId, ':r' => $resourceId, ':h' => $h, ':t' => $t]);
            } catch (\Throwable $e) {
                // If two requests race, a duplicate can still occur; ignore that only.
                $msg = (string)$e->getMessage();
                if (stripos($msg, 'duplicate') === false && stripos($msg, '1062') === false) {
                    throw $e;
                }
            }
        }

        // 4) Update text only if it changed (no id burn)
        $upd = $this->db->prepare(
            "UPDATE i18n_strings
                SET englishText = :t, updatedAt = NOW()
            WHERE clientId = :c AND resourceId = :r AND keyHash = :h
                AND englishText <> :t"
        );
        foreach ($hashToText as $h => $t) {
            if (!isset($haveId[$h])) { continue; } // just inserted; skip redundant update
            if (isset($haveText[$h]) && $haveText[$h] === $t) { continue; }
            $upd->execute([':c' => $clientId, ':r' => $resourceId, ':h' => $h, ':t' => $t]);
        }

        // 5) Re-select mapping to pick up any newly inserted ids
        $sel2 = $this->db->prepare(
            "SELECT stringId, keyHash
            FROM i18n_strings
            WHERE clientId = :c AND resourceId = :r AND keyHash IN ($in)"
        );
        $sel2->execute([':c' => $clientId, ':r' => $resourceId] + $params);

        $hashToId = [];
        while ($row = $sel2->fetch(\PDO::FETCH_ASSOC)) {
            $hashToId[(string)$row['keyHash']] = (int)$row['stringId'];
        }

        // 6) Build stringMap (dot key + sha1 forms) and the numeric id list
        $stringMap = [];
        foreach ($masters as $m) {
            $dot = (string)($m['key']  ?? '');
            $txt = (string)($m['text'] ?? '');
            if ($txt === '') { continue; }
            $hex = sha1($txt);
            $sid = $hashToId[$hex] ?? null;
            if ($sid) {
                if ($dot !== '')        { $stringMap[$dot]      = $sid; }
                $stringMap["sha1:$hex"] = $sid;
                $stringMap[$hex]        = $sid;
            }
        }
        $stringIds = array_values(array_unique(array_values($stringMap)));

        if ($dbg) {
            Log::logDebug('I18nTr-ensure', [
                'masters'   => count($masters),
                'hashes'    => count($hashToText),
                'mapped'    => count($stringIds),
                'sampleKeys'=> array_slice(array_keys($stringMap), 0, 8),
            ]);
        }

        return [$stringMap, $stringIds];
    }


    /** Walk the bundle and return master lines with stable dot-keys. */
    private function extractMasterTexts(array $bundle): array
    {
        $rows = [];
        $this->collectStrings($bundle, [], ['meta'], $rows);
        $out = [];
        foreach ($rows as $r) {
            $out[] = [
                'key'  => implode('.', $r['path']),
                'text' => $r['text'],
            ];
        }

            Log::logDebug('I18nTr-319', 'ensureMastersAndMap', [
                'bundle'   => $bundle,
                'out'    => $out,
            ]);

        return $out;
    }

    private function collectStrings(array $node, array $path, array $skipTopKeys, array &$out): void
    {
        if (empty($path) && is_array($node)) {
            foreach ($skipTopKeys as $skip) { unset($node[$skip]); }
        }
        if (is_array($node)) {
            foreach ($node as $k => $v) {
                $p = [...$path, (string)$k];
                if (is_array($v)) {
                    $this->collectStrings($v, $p, $skipTopKeys, $out);
                } elseif (is_string($v)) {
                    if ($this->looksHumanText($p, $v)) {
                        $out[] = ['path' => $p, 'text' => $v];
                    }
                }
            }
        }
    }

    private function looksHumanText(array $path, string $text): bool
    {
        // ignore empty/whitespace-only values
        if ($text === '' || trim($text) === '') return false;

        $last = (string)end($path);

        // Skip *technical* fields by name, but DO NOT nuke words like "video".
        // - match whole token or token-at-end with common separators
        // - case-insensitive
        $technical = [
            'id', 'ids',
            'code', 'codes',
            'uuid', 'guid',
            'languagecode', 'clientid', 'resourceid', 'stringid',
        ];

        $lastNorm = strtolower($last);

        // exact match (e.g., "id", "code")
        if (in_array($lastNorm, $technical, true)) return false;

        // suffix token (e.g., "clientId", "resource_id", "language-code")
        if (preg_match('/(^|[._-])(id|ids|code|codes|uuid|guid|languagecode|clientid|resourceid|stringid)$/i', $last)) {
            return false;
        }

        return true;
    }


    private function setByPath(array &$arr, array $path, string $value): void
    {
        $ref =& $arr;
        $n = count($path);
        for ($i = 0; $i < $n - 1; $i++) {
            $k = $path[$i];
            if (!isset($ref[$k]) || !is_array($ref[$k])) { $ref[$k] = []; }
            $ref =& $ref[$k];
        }
        $ref[$path[$n - 1]] = $value;
    }

    /** Apply translations onto the bundle using stringId mapping. */
    private function applyTranslationsByStringId(
        array $bundle,
        array $stringMap,
        array $trById
    ): array {
        $out = $bundle;

        // stableKey => translated
        $keyToText = [];
        foreach ($stringMap as $stableKey => $sid) {
            $sid = (int)$sid;
            if (isset($trById[$sid]) && is_string($trById[$sid])) {
                $keyToText[(string)$stableKey] = $trById[$sid];
            }
        }
        if (empty($keyToText)) { return $out; }

        $rows = [];
        $this->collectStrings($bundle, [], ['meta'], $rows);

        foreach ($rows as $r) {
            $path = (array)($r['path'] ?? []);
            $text = (string)($r['text'] ?? '');
            if ($text === '') { continue; }

            $dot    = implode('.', $path);
            $shaHex = sha1($text);
            $shaKey = 'sha1:' . $shaHex;

            $new = $keyToText[$dot]
                ?? $keyToText[$shaKey]
                ?? $keyToText[$shaHex]
                ?? null;

            if ($new !== null) {
                $this->setByPath($out, $path, $new);
            }
        }

        return $out;
    }

    /** Insert/refresh queue rows for missing translations (Google-only). */
    private function enqueueMissing(
        string $clientCode,
        string $resourceType,
        string $subject,
        string $variant,
        string $stringKey,
        string $sourceKeyHash,
        ?int   $sourceStringId,
        string $sourceLanguageGoogle,
        string $targetLanguageGoogle,
        string $sourceText,
        int    $priority = 0
    ): void {
        $sql = "
            INSERT INTO i18n_translation_queue
              (sourceStringId, sourceLanguageCodeGoogle, clientCode,
               resourceType, subject, variant, stringKey, sourceKeyHash,
               targetLanguageCodeGoogle, sourceText, status, runAfter, priority)
            VALUES
              (:sid, :srcG, :client, :rtype, :subj, :var, :skey, :shash,
               :tG, :stext, 'queued', NOW(), :prio)
            ON DUPLICATE KEY UPDATE
              runAfter = LEAST(i18n_translation_queue.runAfter, VALUES(runAfter)),
              priority = LEAST(i18n_translation_queue.priority, VALUES(priority)),
              status   = IF(status='processing','queued',status),
              lockedBy = IF(status='processing',NULL,lockedBy),
              lockedAt = IF(status='processing',NULL,lockedAt)
        ";

        $this->db->executeQuery($sql, [
            ':sid'   => $sourceStringId,
            ':srcG'  => $sourceLanguageGoogle,
            ':client'=> $clientCode,
            ':rtype' => $resourceType,
            ':subj'  => $subject,
            ':var'   => $variant,
            ':skey'  => $stringKey,
            ':shash' => $sourceKeyHash,
            ':tG'    => strtolower($targetLanguageGoogle),
            ':stext' => $sourceText,
            ':prio'  => $priority,
        ]);
    }

    /** Attach/override meta fields neatly. */
    private function withMeta(array $bundle, array $add): array
    {
        $out = $bundle;
        if (!isset($out['meta']) || !is_array($out['meta'])) { $out['meta'] = []; }
        foreach ($add as $k => $v) { $out['meta'][$k] = $v; }

        // Optional font lookup for HL
        if (!isset($out['meta']['font'])) {
            $font = $this->languages->getFontDataFromLanguageCodeHL($out['meta']['languageCodeHL'] ?? '');
            if ($font && $font !== 'null') { $out['meta']['font'] = $font; }
        }

        // cleanup cruft that confuses clients
        if (array_key_exists('langHL', $out['meta'])) { unset($out['meta']['langHL']); }

        return $out;
    }

    // ---- small DB helpers --------------------------------------------------

    /** Build a named-params IN(...) list. */
    private function buildInParams(array $vals, string $prefix = 'p'): array
    {
        $params = []; $ph = []; $i = 0;
        foreach ($vals as $v) {
            $k = ':' . $prefix . $i++;
            $ph[] = $k;
            $params[$k] = (string)$v;
        }
        return [implode(',', $ph), $params];
    }
}
