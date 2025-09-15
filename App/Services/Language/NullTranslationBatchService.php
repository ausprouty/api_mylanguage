<?php
declare(strict_types=1);

namespace App\Services\Language;

class NullTranslationBatchService extends TranslationBatchService
{
    public function __construct(private bool $prefixMode = true) {}

    /** @param array<int,string> $texts */
    public function translateBatch(
        array $texts,
        string $targetLanguage,
        string $sourceLanguage = 'en'
    ): array {
        if ($this->prefixMode) {
            return array_map(
                static fn(string $t) => '[' . $targetLanguage . '] ' . $t,
                $texts
            );
        }
        return $texts;
    }
}
