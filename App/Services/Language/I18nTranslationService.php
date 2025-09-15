<?php
declare(strict_types=1);

namespace App\Services\Translation;

use App\Contracts\Translation\TranslationService;
use App\Repositories\I18nStringsRepository;
use App\Repositories\I18nTranslationsRepository;
use App\Support\Trace;

final class I18nTranslationService implements TranslationService
{
    public function __construct(
        private I18nStringsRepository $strings,
        private I18nTranslationsRepository $translations,
        private string $baseLanguage = 'eng00' // or from config/env
    ) {}

    public function baseLanguage(): string
    {
        return $this->baseLanguage;
    }

    /**
     * Translate a full JSON bundle (interface/commonContent/etc.).
     * Strategy:
     *  - Read meta (kind, subject, variant, client if present)
     *  - Flatten all translatable leaf strings (skip meta & non-text)
     *  - Bulk lookup translation rows for $languageCodeHL (+ variant)
     *  - Rehydrate into original structure
     */
    public function translateBundle(
        array $bundle,
        string $languageCodeHL,
        ?string $variant
    ): array {
        // 0) Short-circuit base language w/ optional variant overlay later
        $isBase = ($languageCodeHL === $this->baseLanguage);

        $meta   = $bundle['meta'] ?? [];
        $kind   = (string)($meta['kind'] ?? '');
        $subject= (string)($meta['subject'] ?? '');
        $client = (string)($meta['client'] ?? ''); // optional
        $variant = ($variant !== null && $variant !== '') ? $variant : null;

        Trace::info('I18nTranslationService.translateBundle start', [
            'kind'    => $kind,
            'subject' => $subject,
            'client'  => $client,
            'lang'    => $languageCodeHL,
            'variant' => $variant,
            'isBase'  => $isBase,
        ]);

        // 1) Flatten translatable strings
        $skipPaths = [
            'meta',          // never translate
            // add other top-level keys to exclude if needed
        ];
        $collect = [];
        $this->collectStrings($bundle, [], $skipPaths, $collect);

        if (empty($collect)) {
            return $bundle; // nothing to translate
        }

        // 2) Find/assign string IDs by EN master text (our “source-of-truth”)
        //    We lookup/create i18n_strings rows keyed by (kind, subject, text)
        //    to keep things stable across clients/variants.
        $stringInfos = $this->strings->ensureIdsForMasterTexts(
            $kind,
            $subject,
            array_values(array_unique(array_map(
                fn ($r) => $r['text'],
                $collect
            )))
        );
        // $stringInfos: text => ['id' => int]

        // 3) Bulk fetch translations for target lang (+ variant or default)
        $stringIds = array_values(array_unique(array_map(
            fn ($r) => $stringInfos[$r['text']]['id'] ?? null,
            $collect
        )));
        $stringIds = array_values(array_filter($stringIds, fn($v) => $v !== null));

        $rows = [];
        if (!$isBase) {
            // Prefer variant translation; fall back to default if missing
            $rows = $this->translations->fetchForLanguage(
                stringIds: $stringIds,
                language: $languageCodeHL,
                client: $client,
                variant: $variant // may be null
            );
        }

        // Map: stringId => translated text
        $trMap = [];
        foreach ($rows as $row) {
            $trMap[(int)$row['stringId']] = (string)$row['text'];
        }

        // 4) Rehydrate translated texts back into the bundle
        $out = $bundle;
        foreach ($collect as $item) {
            $path = $item['path'];
            $src  = $item['text'];
            $sid  = $stringInfos[$src]['id'] ?? null;

            // Choose translated text (non-base) or source (base / fallback)
            $replacement = $src;
            if (!$isBase && $sid !== null && isset($trMap[$sid])) {
                $replacement = $trMap[$sid];
            }

            $this->setByPath($out, $path, $replacement);
        }

        // 5) Mark translation completeness in meta (optional)
        $out['meta']['translationComplete'] =
            $this->isComplete($collect, $stringInfos, $trMap, $isBase);

        return $out;
    }

    /**
     * Collect all leaf-node strings to translate.
     * Produces entries like: ['path' => ['study','title'], 'text' => '...']
     */
    private function collectStrings(
        array $node,
        array $path,
        array $skipTopKeys,
        array &$out
    ): void {
        // Skip top-level excluded keys
        if (empty($path) && is_array($node)) {
            foreach ($skipTopKeys as $skip) {
                if (array_key_exists($skip, $node)) {
                    // leave meta untouched
                }
            }
        }

        // Recurse
        if (is_array($node)) {
            foreach ($node as $k => $v) {
                // Skip top-level blocks like 'meta'
                if (empty($path) && in_array((string)$k, $skipTopKeys, true)) {
                    continue;
                }

                $p = [...$path, (string)$k];
                if (is_array($v)) {
                    $this->collectStrings($v, $p, $skipTopKeys, $out);
                } elseif (is_string($v)) {
                    // Only collect human-facing strings (heuristic)
                    if ($this->looksHumanText($p, $v)) {
                        $out[] = ['path' => $p, 'text' => $v];
                    }
                }
            }
        }
    }

    private function looksHumanText(array $path, string $text): bool
    {
        // Heuristics: ignore empty, ids, codes, machine-y values
        if ($text === '' || trim($text) === '') {
            return false;
        }
        $last = strtolower((string)end($path));
        if (str_contains($last, 'code') || str_contains($last, 'id')) {
            return false;
        }
        return true;
    }

    /** Set $value at $path inside $arr (by reference). */
    private function setByPath(array &$arr, array $path, string $value): void
    {
        $ref =& $arr;
        $n   = count($path);
        for ($i = 0; $i < $n - 1; $i++) {
            $key = $path[$i];
            if (!isset($ref[$key]) || !is_array($ref[$key])) {
                $ref[$key] = [];
            }
            $ref =& $ref[$key];
        }
        $ref[$path[$n - 1]] = $value;
    }

    private function isComplete(
        array $collected,
        array $stringInfos,
        array $trMap,
        bool $isBase
    ): bool {
        if ($isBase) {
            return true;
        }
        foreach ($collected as $item) {
            $src = $item['text'];
            $sid = $stringInfos[$src]['id'] ?? null;
            if ($sid === null || !isset($trMap[$sid])) {
                return false;
            }
        }
        return true;
    }
}
