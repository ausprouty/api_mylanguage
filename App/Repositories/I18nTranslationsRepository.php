<?php
declare(strict_types=1);

namespace App\Repositories;

interface I18nTranslationsRepository
{
    /**
     * Fetch best-available translations:
     *  - If $variant provided, prefer that; otherwise default variant.
     *  - If $client provided, prefer client-specific; else global.
     * Return rows: ['stringId'=>int,'text'=>string]
     */
    public function fetchForLanguage(
        array $stringIds,
        string $language,
        ?string $client,
        ?string $variant
    ): array;
}
