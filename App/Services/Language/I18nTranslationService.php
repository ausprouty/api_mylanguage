<?php

declare(strict_types=1);
namespace App\Services\Language;

use App\Contracts\Translation\TranslationService as TranslationServiceContract;
use App\Repositories\I18nStringsRepository;
use App\Repositories\I18nTranslationsRepository;
use App\Repositories\I18nClientsRepository;
use App\Repositories\I18nResourcesRepository;
use App\Repositories\LanguageRepository;

use App\Services\LoggerService;
use App\Services\Database\DatabaseService;
use App\Support\Async;

class I18nTranslationService implements TranslationServiceContract
{
    public function __construct(
        private I18nStringsRepository       $strings,
        private I18nTranslationsRepository  $translations,
        private I18nClientsRepository       $clients,
        private I18nResourcesRepository     $resources,
        private DatabaseService             $db,
        private LanguageRepository          $languages,
        private string                      $baseLanguage = 'eng00'
    ) {}

    public function baseLanguage(): string
    {
        return $this->baseLanguage;
    }

   public function translateBundle(
        array $bundle,
        string $languageCodeHL,
        ?string $variant,
        array $ctx = []
    ): array {
        // Required context from resolver
        $type            = (string)($ctx['kind']             ?? 'interface');          // i18n_resources.type
        $resourceSubject = (string)($ctx['resourceSubject']  ?? 'app');                // i18n_resources.subject
        $resourceVariant = (string)($ctx['resourceVariant']  ?? ($variant ?: 'default')); // i18n_resources.variant
        $clientCode      = (string)($ctx['clientCode']       ?? 'wsu');                // i18n_clients.clientCode
        $isBase          = (bool)  ($ctx['isBase']           ?? ($languageCodeHL === $this->baseLanguage));
        $normVariant     = (string)($ctx['variant']          ?? ($variant ?: 'default'));

        // Resolve IDs
        $clientId = $this->clients->getIdByCode($clientCode);
        if (!$clientId) {
            throw new \RuntimeException("Unknown clientCode '{$clientCode}'");
        }
        $resourceId = $this->resources->getIdByTypeSubjectVariant($type, $resourceSubject, $resourceVariant);
        if (!$resourceId) {
            throw new \RuntimeException("Unknown resource {$type}/{$resourceSubject}/{$resourceVariant}");
        }

        // Extract master texts and compute deterministic key hashes.
        // Your repo can decide: use key-paths or english text as the hash basis.
        // This call should return an array like:
        //   [
        //     ['key' => 'interface.share.button', 'text' => 'Share this with a friend', 'note' => 'Button label'],
        //     ...
        //   ]
        $masters = $this->extractMasterTexts($bundle);

        // Upsert strings and get back stringIds keyed by hash
        // Repo contract suggestion:
        //   ensureIdsForMasterTexts(int $clientId, int $resourceId, array $masters): array
        // Return: ['<keyHash>' => <stringId>, ...]
        $stringMap = $this->strings->ensureIdsForMasterTexts(
            clientId:   (int)$clientId,
            resourceId: (int)$resourceId,
            masters:    $masters
        );

        if ($isBase) {
            // For ENG: we've seeded stringIds already; return bundle as-is
            return $bundle;
        }

        // langauge details
        [$languageName, $languageCodeIso] = $this->resolveIsoAndName($languageCodeHL) ?? [null, null];

        // Fetch translations by stringId + HL language
        $stringIds = array_values($stringMap);
        if (empty($stringIds)) {
            return $bundle;
        }
        // Fetch by HL first
        $rows = $this->translations->fetchByStringIdsAndLanguage(
            stringIds: $stringIds,
            languageCodeHL:  $languageCodeHL
        );
        $iso2 = $this->languages->getCodeIsoFromCodeHL($languageCodeHL) ?? substr($languageCodeHL, 0, 2);
        if (empty($rows)) {
            
            if (method_exists($this->translations, 'fetchByStringIdsAndLanguageIso')) {
                $rows = $this->translations->fetchByStringIdsAndLanguageIso(
                    stringIds:         $stringIds,
                    languageCodeIso:   $languageCodeIso
                );
            }
        }

        // Map stringId -> translatedText
        $trById = [];
        foreach ($rows as $r) {
            $sid = (int)$r['stringId'];
            $trById[$sid] = (string)$r['translatedText'];
        }
        +        // --- Compute counts & enqueue missing (for non-base languages) ---
        $keysTotal     = count($masters);
        $translatedCnt = 0;
        $missingRows   = [];
        $srcIso        = strtolower(substr($this->baseLanguage, 0, 2));
        $tgtIso        = strtolower(substr($languageCodeHL, 0, 2));

        foreach ($masters as $m) {
            $dot = (string)($m['key']  ?? '');
            $txt = (string)($m['text'] ?? '');
            if ($txt === '') continue;
            $shaHex = sha1($txt);
            $shaKey = 'sha1:' . $shaHex; // keep prefixed form for lookups
             // tolerate any of the three key styles
            $sid = $stringMap[$dot] ?? $stringMap[$shaKey] ?? $stringMap[$shaHex] ?? null;
            $has = ($sid !== null) && array_key_exists((int)$sid, $trById) && $trById[(int)$sid] !== '';
            if ($has) {
                $translatedCnt;
            } elseif (!$isBase) {
                $missingRows[] = [
                    // Prefer dot-path as the human key; fall back to prefixed key
                    'stringKey' => $dot !== '' ? $dot : $shaKey,
+                   'keyHash'   => $shaHex, // DB wants 40-hex
                    'sid'       => $sid ? (int)$sid : null,
                    'text'      => $txt,
                ];
            }
        }

        if (!$isBase && $missingRows) {
            foreach ($missingRows as $mr) {
                $this->enqueueMissing(
                    clientCode:        $clientCode,
                    resourceType:      $type,
                    subject:           $resourceSubject,
                    variant:           $resourceVariant,
                    stringKey:         $mr['stringKey'],
                    sourceKeyHash:     $mr['keyHashHex'],
                    sourceStringId:    $mr['sid'],
                    sourceLanguageIso: $srcIso,
                    targetLanguageIso: $tgtIso,
                    sourceText:        $mr['text'],
                    priority:          0
                );
            }
            Async::php(__DIR__ . '/../../../bin/translate-queue.php', [
                '--once',
                '--lang=' . $languageCodeHL,
                '--client=' . $clientCode,
                '--type=' . $type,
                '--subject=' . $resourceSubject,
                '--variant=' . $resourceVariant,
            ]);
        }

        // Apply translations back onto the bundle nodes using the same hash mapping
        $out = $this->applyTranslationsByStringId($bundle, $stringMap, $trById);
 
        // Optional: annotate meta for debugging
        if (isset($out['meta']) && is_array($out['meta'])) {
            $out['meta']['resourceSubject'] = $resourceSubject;
            $out['meta']['resourceVariant'] = $resourceVariant;
            $out['meta']['clientCode']      = $clientCode;
            $out['meta']['languageCodeHL']  = $languageCodeHL;
            $out['meta']['variant']         = $normVariant;

            $font = $this->languages->getFontDataFromLanguageCodeHL($languageCodeHL);
            if ($font) $out['meta']['font'] = $font;

            
            if ($name) $out['meta']['languageName']    = $languageName;
            if ($languageCodeIso)  $out['meta']['languageCodeISO'] = $iso;

             // Update translation progress meta
            $out['meta']['keysTotal']       = $keysTotal;
            $out['meta']['keysMissing']     = max(0, $keysTotal - $translatedCnt);
            $out['meta']['keysFuzzy']       = $out['meta']['keysFuzzy'] ?? 0;
            $out['meta']['translationComplete'] = 
                ($out['meta']['keysMissing'] === 0);
            // cleanup
             unset($out['meta']['langHL']);
             if (($out['meta']['font'] ?? null) === 'null') {
                unset($out['meta']['font']);
            }

            
        }

        return $out;
    }

    // -------- internals --------

    private function collectStrings(array $node, array $path, array $skipTopKeys, array &$out): void
    {
        if (empty($path) && is_array($node)) {
            foreach ($skipTopKeys as $skip) unset($node[$skip]);
        }
        if (is_array($node)) {
            foreach ($node as $k => $v) {
                $p = [...$path, (string)$k];
                if (is_array($v)) {
                    $this->collectStrings($v, $p, $skipTopKeys, $out);
                } elseif (is_string($v)) {
                    if ($this->looksHumanText($p, $v)) $out[] = ['path' => $p, 'text' => $v];
                }
            }
        }
    }

    private function looksHumanText(array $path, string $text): bool
    {
        if ($text === '' || trim($text) === '') return false;
        $last = strtolower((string)end($path));
        if (str_contains($last, 'code') || str_contains($last, 'id')) return false;
        return true;
    }

    private function setByPath(array &$arr, array $path, string $value): void
    {
        $ref =& $arr;
        $n = count($path);
        for ($i = 0; $i < $n - 1; $i++) {
            $k = $path[$i];
            if (!isset($ref[$k]) || !is_array($ref[$k])) $ref[$k] = [];
            $ref =& $ref[$k];
        }
        $ref[$path[$n - 1]] = $value;
    }

    private function enqueueMissing(
        string $clientCode,
        string $resourceType,
        string $subject,
        string $variant,
        string $stringKey,
        string $sourceKeyHash,
        ?int   $sourceStringId,
        string $sourceLanguageIso,
        string $targetLanguageIso,
        string $sourceText,
        int    $priority = 0
    ): void {
        $sql = "
        INSERT INTO i18n_translation_queue
          (sourceStringId, sourceLanguageCodeIso, clientCode,
           resourceType, subject, variant, stringKey, sourceKeyHash,
           targetLanguageCodeIso, sourceText, status, runAfter, priority)
        VALUES
          (:sid, :srcIso, :client, :rtype, :subj, :var, :skey, :shash,
           :tIso, :stext, 'queued', NOW(), :prio)
        ON DUPLICATE KEY UPDATE
          runAfter = LEAST(i18n_translation_queue.runAfter, VALUES(runAfter)),
          priority = LEAST(i18n_translation_queue.priority, VALUES(priority))
        ";
        $this->db->executeQuery($sql, [
            ':sid'    => $sourceStringId,
            ':srcIso' => $sourceLanguageIso,
            ':client' => $clientCode,
            ':rtype'  => $resourceType,
            ':subj'   => $subject,
            ':var'    => $variant,
            ':skey'   => $stringKey,
            ':shash'  => $sourceKeyHash,
            ':tIso'   => $targetLanguageIso,
            ':stext'  => $sourceText,
            ':prio'   => $priority,
        ]);
    }
    /**
     * Walk the bundle and return a flat list of master texts with stable keys.
     * Each item: ['key' => 'a.b.c', 'text' => '...']
     */
    private function extractMasterTexts(array $bundle): array
    {
        $rows = [];
        // Skip meta at the top level; keep everything else
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

     /**
     * Apply translations back onto the bundle.
     *
     * @param array $bundle   Original assembled bundle.
     * @param array $stringMap Map: stableKey => stringId. The stableKey can be
     *                         a dot-path (e.g., 'interface.share') or a text
     *                         hash in the form 'sha1:<hex>'.
     * @param array $trById    Map: stringId (int) => translated text (string).
     * @return array           Bundle with translated strings applied.
     */
    private function applyTranslationsByStringId(
        array $bundle,
        array $stringMap,
        array $trById
    ): array {
        $out = $bundle;
        LoggerService::logInfo('I18nTranslatioService-290', $out);
        // Build stableKey => translatedText index.
        $keyToText = [];
        foreach ($stringMap as $stableKey => $sid) {
            $sid = (int) $sid;
            if (isset($trById[$sid]) && is_string($trById[$sid])) {
                $keyToText[(string) $stableKey] = $trById[$sid];
            }
        }
        if (empty($keyToText)) {
            return $out;
        }

        // Collect all candidate strings (skip top-level 'meta').
        $rows = [];
        $this->collectStrings($bundle, [], ['meta'], $rows);

        foreach ($rows as $r) {
            $path = (array) ($r['path'] ?? []);
            $text = (string) ($r['text'] ?? '');
            if ($text === '') {
                continue;
            }

            // Two possible stable keys:
            //  1) dot-path: a.b.c
            //  2) text hash: sha1:<hex>
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
        LoggerService::logInfo('I18nTranslatioService-324', $out);
        return $out;
    }

    private function resolveIsoAndName(string $languageCodeHL): array
    {
        try {
            // ISO first (null if missing)
            $iso = $this->languages->getCodeIsoFromCodeHL($languageCodeHL);
            // Prefer name by HL, fall back to name by ISO
            $name = $this->languages->getEnglishNameForLanguageCodeHL($languageCodeHL)
                 ?: ($iso ? $this->languages->getEnglishNameForLanguageCodeIso($iso) : null);
            return [$name, $iso];
        } catch (\Throwable $e) {
            // Don't fail the request if lookup bombs; just return what we can.
            return [null, null];
        }
    }
    /** Fallback for stores that index translations by ISO instead of HL. */
    public function fetchByStringIdsAndLanguageIso(array $stringIds, string $languageCodeIso): array
    {
        if (empty($stringIds)) return [];
        $placeholders = [];
        $params = [':iso' => $languageCodeIso];
        foreach ($stringIds as $i => $id) {
            $ph = ':id'.$i;
            $placeholders[] = $ph;
            $params[$ph] = (int)$id;
        }
        $in = implode(',', $placeholders);
        $sql = "SELECT stringId, translatedText
                  FROM i18n_translations
                 WHERE stringId IN ($in)
                   AND languageCodeIso = :iso";
        return $this->databaseService->fetchAll($sql, $params);
    }

    

}
