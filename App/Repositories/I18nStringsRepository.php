<?php
declare(strict_types=1);

namespace App\Repositories;

interface I18nStringsRepository
{
    /**
     * Ensure each EN master text has a stable stringId for (kind, subject).
     * Returns map: text => ['id' => int]
     */
    public function ensureIdsForMasterTexts(
        string $kind,
        string $subject,
        array $masterTexts
    ): array;
}

