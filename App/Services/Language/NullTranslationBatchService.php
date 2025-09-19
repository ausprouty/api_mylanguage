<?php
declare(strict_types=1);

namespace App\Services\Language;

/**
 * Null/dummy translator for smoke tests. It never calls external APIs.
 * - When prefixMode=true, it prefixes each string with "[<tgt>] ".
 * - Otherwise, it returns texts unchanged.
 *
 * NOTE: TranslationBatchService is a class (not an interface), so we extend it.
 */
final class NullTranslationBatchService extends TranslationBatchService
{
    public function __construct(private bool $prefixMode = false) {}

    /**
     * @param array<int|string,string> $texts
     * @return array<int|string,string>
     */
    public function translateBatch(
        array $texts,
        string $targetLanguage,
        string $sourceLanguage = 'en',
        string $format = 'text'
    ): array {
        if (!$this->prefixMode) {
            // Preserve original keys; ensure string-cast
            foreach ($texts as $k => $v) {
                $texts[$k] = (string)$v;
            }
            return $texts;
        }
        $out = [];
        foreach ($texts as $k => $t) {
            $out[$k] = '[' . $targetLanguage . '] ' . (string)$t;
        }
        return $out;
    }
}