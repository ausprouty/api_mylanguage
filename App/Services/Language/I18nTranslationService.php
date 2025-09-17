<?php
declare(strict_types=1);

namespace App\Services\Language;

use App\Contracts\Translation\TranslationService;
use App\Repositories\I18nStringsRepository;
use App\Repositories\I18nTranslationsRepository;
use App\Repositories\LanguageRepository;
use App\Services\Database\DatabaseService;
use App\Support\Trace;

/**
 * I18nTranslationService (no TranslationMemoryService)
 *
 * - Flatten → ensure IDs in i18n_strings → bulk fetch i18n_translations
 * - Fallback to English, enqueue misses to i18n_translation_queue
 */
class I18nTranslationService implements TranslationService
{
    public function __construct(
        private I18nStringsRepository      $strings,
        private I18nTranslationsRepository $translations,
        private DatabaseService            $db,
        private LanguageRepository         $languages,
        private string                     $baseLanguage = 'eng00'
    ) {}

    public function baseLanguage(): string
    {
        return $this->baseLanguage;
    }

    public function translateBundle(
        array $bundle,
        string $languageCodeHL,
        ?string $variant
    ): array {
        $isBase  = ($languageCodeHL === $this->baseLanguage);

        $meta     = $bundle['meta'] ?? [];
        $kind     = (string)($meta['kind']    ?? '');
        $subject  = (string)($meta['subject'] ?? '');
        $client   = (string)($meta['client']  ?? '');
        $variant  = ($variant !== null && $variant !== '') ? $variant : null;

        Trace::info('I18nTranslationService.translateBundle', [
            'kind'    => $kind,
            'subject' => $subject,
            'client'  => $client,
            'langHL'  => $languageCodeHL,
            'variant' => $variant,
            'isBase'  => $isBase,
        ]);

        // 1) Flatten (skip meta)
        $skipTop = ['meta'];
        $collected = [];
        $this->collectStrings($bundle, [], $skipTop, $collected);

        if (empty($collected)) {
            $bundle['meta']['translationComplete'] = true;
            return $bundle;
        }

        // 2) Ensure string IDs for EN masters
        $uniqueTexts = array_values(array_unique(array_map(
            fn(array $r) => $r['text'], $collected
        )));
        $stringInfos = $this->strings->ensureIdsForMasterTexts($kind, $subject, $uniqueTexts);

        // 3) Bulk fetch translations for target HL (+ variant if repo supports)
        $stringIds = array_values(array_unique(array_map(
            fn($r) => $stringInfos[$r['text']]['id'] ?? null, $collected
        )));
        $stringIds = array_values(array_filter($stringIds, fn($v) => $v !== null));

        $rows = [];
        if (!$isBase && !empty($stringIds)) {
            $rows = $this->translations->fetchForLanguage(
                stringIds: $stringIds,
                language:  $languageCodeHL,
                client:    $client,
                variant:   $variant
            );
        }

        // Map: stringId => translated text
        $trMap = [];
        foreach ($rows as $row) {
            $sid = (int)$row['stringId'];
            $txt = (string)$row['text'];
            if ($txt !== '') $trMap[$sid] = $txt;
        }

        // ISO for queue
        $isoCode = $this->languages->getCodeIsoFromCodeHL($languageCodeHL)
                   ?? $this->db->fetchValue(
                        'SELECT languageCodeIso FROM hl_languages WHERE languageCodeHL = :hl LIMIT 1',
                        [':hl' => $languageCodeHL]
                      )
                   ?? 'en';

        // 4) Rehydrate; enqueue misses
        $out = $bundle;
        $complete = true;

        foreach ($collected as $item) {
            $path = $item['path'];
            $src  = $item['text'];
            $sid  = $stringInfos[$src]['id'] ?? null;

            if (!$isBase && $sid !== null && isset($trMap[$sid]) && $trMap[$sid] !== '') {
                $this->setByPath($out, $path, $trMap[$sid]);
            } else {
                if (!$isBase) {
                    $complete = false;
                    $this->enqueueMissing(
                        clientCode:         $client,
                        resourceType:       $kind,
                        subject:            $subject,
                        variant:            (string)($variant ?? ''),
                        stringKey:          implode('.', $path),
                        sourceKeyHash:      sha1($src),
                        sourceStringId:     $sid,
                        sourceLanguageIso:  'en',
                        targetLanguageIso:  $isoCode,
                        sourceText:         $src
                    );
                }
                $this->setByPath($out, $path, $src); // English fallback
            }
        }

        $out['meta']['translationComplete'] = $isBase ? true : $complete;
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
}
