<?php
declare(strict_types=1);

namespace App\Contracts\Translation;

interface TranslationProvider
{
     /** @param array<int,string> $texts */
    /**
     * @throws \Exception on failure
     */
    
    public function translate(
        array $texts,
        string $targetLanguage,
        string $sourceLanguage = 'en',
        string $format = 'text'
    ): array;
}
