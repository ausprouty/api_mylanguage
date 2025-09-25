<?php
declare(strict_types=1);

namespace App\Contracts\Translation;

interface TranslationProvider
{
    /**
     * @throws \Exception on failure
     */
    public function translate(
        string $sourceLangGoogle,
        string $targetLangGoogle,
        string $text
    ): string;
}
