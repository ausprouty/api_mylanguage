<?php

namespace App\Repositories;

use App\Models\Bible\BibleModel;
use App\Services\Database\DatabaseService;

/**
 * Handles BibleBrain-specific interactions with the hl_languages table,
 * such as syncing language metadata and flags.
 */
class BibleBrainBibleRepository extends BaseRepository
{
    public function __construct(DatabaseService $databaseService)
    {
        parent::__construct($databaseService);
    }

    

    /**
     * Retrieves HL and BibleBrain language codes from ISO code.
     */
    public function getLanguageCodesFromBibleBrain(string $languageCodeBibleBrain): ?array
    {
        $query = 'SELECT languageCodeHL, languageCodeJF
                  FROM hl_languages
                  WHERE languageCodeBibleBrain = :languageCodeBibleBrain LIMIT 1';
        return $this->databaseService->fetchRow($query, [':languageCodeBibleBrain' => $languageCodeBibleBrain]);
    }

    
    /**
     * Checks if a BibleBrain language ID exists in the database.
     */
    public function bibleBrainBibleRecordExists(string $languageCodeBibleBrain): bool
    {
        $query = 'SELECT id FROM bible 
                  WHERE languageCodeBibleBrain = :languageCodeBibleBrain 
                  LIMIT 1';
        return $this->databaseService->fetchSingleValue(
            $query,
            [':languageCodeBibleBrain' => $languageCodeBibleBrain]
        ) !== null;
    }

    /**
     * Clears the CheckedBBBibles field for all languages.
     */
    public function clearCheckedBBBibles(): void
    {
        $this->databaseService->executeQuery('UPDATE hl_languages SET CheckedBBBibles = NULL');
    }

    /**
     * Retrieves the next languageCodeBibleBrain needing Bible detail.
     */
    public function getLanguageCodesForNextBibleDetails(): ?array
    {
        $query = 'SELECT languageCodeHL, languageCodeIso, languageCodeJF, languageCodeBibleBrain
                  FROM hl_languages 
                  WHERE languageCodeBibleBrain IS NOT NULL 
                    AND CheckedBBBibles IS NULL 
                  LIMIT 1';
        return $this->databaseService->fetchRow($query);
    }
}
