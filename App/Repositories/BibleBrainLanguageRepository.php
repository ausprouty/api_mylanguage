<?php

namespace App\Repositories;

use App\Models\Language\LanguageModel;
use App\Services\Database\DatabaseService;

/**
 * Handles BibleBrain-specific interactions with the hl_languages table,
 * such as syncing language metadata and flags.
 */
class BibleBrainLanguageRepository extends BaseRepository
{
    public function __construct(DatabaseService $databaseService)
    {
        parent::__construct($databaseService);
    }

    /**
     * Inserts a new language record using BibleBrain data.
     */
    public function createLanguageFromBibleBrainRecord(LanguageModel $language): void
    {
        $query = 'INSERT INTO hl_languages (languageCodeBibleBrain, languageCodeIso, name, ethnicName) 
                  VALUES (:languageCodeBibleBrain, :languageCodeIso, :name, :ethnicName)';
        $params = [
            ':languageCodeBibleBrain' => $language->getLanguageCodeBibleBrain(),
            ':languageCodeIso' => $language->getLanguageCodeIso(),
            ':name' => $language->getName(),
            ':ethnicName' => $language->getEthnicName()
        ];
        $this->databaseService->executeQuery($query, $params);
    }

    /**
     * Updates the BibleBrain code for a language by ISO code.
     */
    public function updateLanguageCodeBibleBrain(string $languageCodeIso, string $languageCodeBibleBrain): void
    {
        $query = 'UPDATE hl_languages 
                  SET languageCodeBibleBrain = :languageCodeBibleBrain 
                  WHERE languageCodeIso = :languageCodeIso';
        $params = [
            ':languageCodeBibleBrain' => $languageCodeBibleBrain,
            ':languageCodeIso' => $languageCodeIso
        ];
        $this->databaseService->executeQuery($query, $params);
    }

    /**
     * Checks if a BibleBrain language ID exists in the database.
     */
    public function bibleBrainLanguageRecordExists(string $languageCodeBibleBrain): bool
    {
        $query = 'SELECT id FROM hl_languages 
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
     * Retrieves the next languageCodeIso needing BibleBrain detail processing.
     */
    public function getNextLanguageForLanguageDetails(): ?string
    {
        $query = 'SELECT languageCodeIso 
                  FROM hl_languages 
                  WHERE languageCodeBibleBrain IS NULL 
                    AND CheckedBBBibles IS NOT NULL 
                  LIMIT 1';
        return $this->databaseService->fetchColumn($query);
    }
}
