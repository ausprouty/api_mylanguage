<?php

declare(strict_types=1);

namespace App\Services\Language;

use App\Contracts\Translation\TranslationService as TranslationServiceContract;
use App\Repositories\I18nStringsRepository;
use App\Repositories\I18nTranslationsRepository;
use App\Repositories\I18nClientsRepository;
use App\Repositories\I18nResourcesRepository;
use App\Repositories\LanguageRepository;
use App\Services\Database\DatabaseService;
use App\Services\LoggerService;
use App\Services\LoggerService as Log;
use App\Support\Async;
use App\Configuration\Config;

class I18nTranslationService implements TranslationServiceContract
{
    public function __construct(
        private I18nStringsRepository      $strings,
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
     * Translate a resolved content bundle into the requested HL language.
     * Google-only (languageCodeGoogle); English source is the fallback.
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

        // ---- extract masters (English source lines) ------------------------
        if ($dbg) { Log::logDebug('I18nTr-076', 'bundle', $bundle); }
        $masters = $this->extractMasterTexts($bundle); // [['key' => 'a.b', 'text' => '...'], ...]
        if ($dbg) { Log::logDebug('I18nTr-077', 'masters', $masters); }

        // ---- resolve Google code from HL -----------------------------------
        $google = $this->languages->getCodeGoogleFromCodeHL($languageCodeHL) ?? '';
        if ($google === '') {
            $google = strtolower(substr($languageCodeHL, 0, 2)); // best-effort
        }

        // ---- base language: seed ids and return as-is -----------------------
        if ($isBase) {
            // Ensure IDs exist even for base language (keeps catalog in sync)
            $this->ensureStringIds($clientId, $resourceId, $masters, $dbg);
            return $this->withMeta($bundle, [
                'resourceSubject'  => $resourceSubject,
                'resourceVariant'  => $resourceVariant,
                'clientCode'       => $clientCode,
                'languageCodeHL'   => $languageCodeHL,
                'languageCodeGoogle' => 'en',
                'variant'          => $normVariant,
                'keysTotal'        => count($masters),
                'keysMissing'      => 0,
                'keysFuzzy'        => $bundle['meta']['keysFuzzy'] ?? 0,
                'translationComplete' => true,
            ]);
        }

        // ---- ensure strings exist, get mapping + ids ------------------------
        [$stringMap, $stringIds] = $this->ensureStringIds($clientId, $resourceId, $masters, $dbg);
        if (empty($stringIds)) {
            return $this->withMeta($bundle, [
                'resourceSubject'   => $resourceSubject,
                'resourceVariant'   => $resourceVariant,
                'clientCode'        => $clientCode,
                'languageCodeHL'    => $languageCodeHL,
                'languageCodeGoogle'=> $google,
                'variant'           => $normVariant,
                'keysTotal'         => 0,
                'keysMissing'       => 0,
                'keysFuzzy'         => $bundle['meta']['keysFuzzy'] ?? 0,
                'translationComplete' => true,
            ]);
        }

        if ($dbg) { Log::logDebug('I18nTr-120', 'stringIds', $stringIds); }

        // ---- fetch Google translations for those ids ------------------------
        $rowsGoogle = $this->translations->fetchByStringIdsAndLanguageGoogle($stringIds, $google);
        if ($dbg) { Log::logDebug('I18nTr-126', 'rowsGoogle', $rowsGoogle); }

        $trById = [];
        foreach ($rowsGoogle as $r) {
            $sid = (int)($r['stringId'] ?? 0);
            if ($sid > 0) {
                $trById[$sid] = (string)($r['translatedText'] ?? '');
            }
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

            // find stringId for this line
            $sid = $stringMap[$dot] ?? $stringMap[$shaKey] ?? $stringMap[$shaHex] ?? null;
            $sid = ($sid !== null) ? (int)$sid : null;

            $applied = ($sid !== null && isset($trById[$sid])) ? (string)$trById[$sid] : '';
            $isTranslated = ($applied !== '') && ($norm($applied) !== $norm($txt));

            if ($isTranslated) {
                $translatedCnt++;
            } else {
                $missingRows[] = [
                    'stringKey' => ($dot !== '' ? $dot : $shaKey),
                    'keyHash'   => $shaHex,
                    'sid'       => $sid,
                    'text'      => $txt,
                ];
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

            // Kick a one-shot worker (optional)
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
            'resourceSubject'     => $resourceSubject,
            'resourceVariant'     => $resourceVariant,
            'clientCode'          => $clientCode,
            'languageCodeHL'      => $languageCodeHL,
            'languageCodeGoogle'  => $google,
            'variant'             => $normVariant,
            'keysTotal'           => $keysTotal,
            'keysMissing'         => $keysMissing,
            'keysFuzzy'           => $out['meta']['keysFuzzy'] ?? 0,
            'translationComplete' => ($keysMissing === 0),
            'fallbackCount'       => $keysMissing, // number of English fallbacks used
        ]);

        return $out;
    }

    // ---------------------------------------------------------------------
    // internals
    // ---------------------------------------------------------------------

    /**
     * Ensure every master line exists in i18n_strings and return:
     *   [$stringMap, $stringIds]
     * $stringMap is keyed by dot key, "sha1:<hex>", and "<hex>".
     */
    private function ensureStringIds(
        int $clientId,
        int $resourceId,
        array $masters,
        bool $dbg = false
    ): array {
        // Prefer the richer helper if your repository has it
        if (\method_exists($this->strings, 'ensureAndMapForMasters')) {
            [$stringMap, $stringIds] = $this->strings->ensureAndMapForMasters($clientId, $resourceId, $masters);
            if ($dbg) {
                Log::logDebug('I18nTr-115', 'ensureAndMapForMasters', ['ids' => count($stringIds)]);
            }
            return [$stringMap, $stringIds];
        }

        // Fallback: ensureIdsForMasterTexts returns keyHash=>stringId
        $hashMap = $this->strings->ensureIdsForMasterTexts(
            clientId:   $clientId,
            resourceId: $resourceId,
            masters:    $masters
        ); // ['<hex>' => int]

        // Build a map that also includes dot and "sha1:<hex>" keys
        $stringMap = [];
        foreach ($masters as $m) {
            $dot = (string)($m['key']  ?? '');
            $txt = (string)($m['text'] ?? '');
            if ($txt === '') { continue; }
            $hex = sha1($txt);
            $sid = $hashMap[$hex] ?? null;
            if ($sid) {
                if ($dot !== '' && !isset($stringMap[$dot])) { $stringMap[$dot] = $sid; }
                $stringMap["sha1:$hex"] = $sid;
                $stringMap[$hex]        = $sid;
            }
        }
        $stringIds = array_values(array_unique(array_values($stringMap)));

        if ($dbg) {
            $sampleKeys = array_slice(array_keys($stringMap), 0, 5);
            $sampleVals = array_slice(array_values($stringMap), 0, 5);
            Log::logDebug('I18nTr-119', 'stringMap.sample', [
                'keys' => $sampleKeys, 'sids' => $sampleVals, 'total' => count($stringMap)
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
        if ($text === '' || trim($text) === '') { return false; }
        $last = strtolower((string)end($path));
        if (str_contains($last, 'code') || str_contains($last, 'id')) { return false; }
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
            $sid = (int) $sid;
            if (isset($trById[$sid]) && is_string($trById[$sid])) {
                $keyToText[(string)$stableKey] = $trById[$sid];
            }
        }
        if (empty($keyToText)) {
            return $out;
        }

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
              -- if a worker crashed mid-flight, release it
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
        if (!isset($out['meta']) || !is_array($out['meta'])) {
            $out['meta'] = [];
        }
        foreach ($add as $k => $v) {
            $out['meta'][$k] = $v;
        }

        // Optional: attach font data for this HL (if available)
        if (!isset($out['meta']['font'])) {
            $font = $this->languages->getFontDataFromLanguageCodeHL($out['meta']['languageCodeHL'] ?? '');
            if ($font && $font !== 'null') {
                $out['meta']['font'] = $font;
            }
        }

        // cleanup
        if (array_key_exists('langHL', $out['meta'])) {
            unset($out['meta']['langHL']);
        }

        return $out;
    }
}
