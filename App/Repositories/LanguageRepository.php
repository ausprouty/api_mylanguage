<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Factories\LanguageModelFactory;
use App\Models\Language\LanguageModel;

/**
 * Handles database operations for the LanguageModel.
 */
class LanguageRepository
{
    private $databaseService;
    private $languageModelFactory;

    /**
     * Constructor to initialize dependencies.
     */
    public function __construct(
        DatabaseService $databaseService,
        LanguageModelFactory $languageModelFactory
    ) {
        $this->databaseService = $databaseService;
        $this->languageModelFactory = $languageModelFactory;
    }

    /**
     * Checks if a language record exists by BibleBrain language code.
     */
    public function bibleBrainLanguageRecordExists(
        string $languageCodeBibleBrain
    ): bool {
        $query = 'SELECT id FROM hl_languages WHERE languageCodeBibleBrain = '
               . ':languageCodeBibleBrain LIMIT 1';
        $params = [':languageCodeBibleBrain' => $languageCodeBibleBrain];
        return $this->databaseService->fetchSingleValue($query, $params) 
               !== null;
    }

    /**
     * Clears the CheckedBBBibles field for all languages.
     */
    public function clearCheckedBBBibles(): void
    {
        $query = 'UPDATE hl_languages SET CheckedBBBibles = NULL';
        $this->databaseService->executeQuery($query);
    }

    /**
     * Creates a new language record from a LanguageModel.
     */
    public function createLanguageFromBibleBrainRecord(
        LanguageModel $language
    ): void {
        $query = 'INSERT INTO hl_languages (languageCodeBibleBrain, 
                    languageCodeIso, name, ethnicName) 
                  VALUES (:languageCodeBibleBrain, :languageCodeIso, 
                    :name, :ethnicName)';
        $params = [
            ':languageCodeBibleBrain' => $language->getLanguageCodeBibleBrain(),
            ':languageCodeIso' => $language->getLanguageCodeIso(),
            ':name' => $language->getName(),
            ':ethnicName' => $language->getEthnicName()
        ];
        $this->databaseService->executeQuery($query, $params);
    }

    /**
     * Finds a language by a specific source code using the factory.
     */
    public function findOneByCode(
        string $source,
        string $code
    ): ?LanguageModel {
        return $this->languageModelFactory->findOneByCode($source, $code);
    }

    /**
     * Finds a language by its HL code using the factory.
     */
    public function findOneByLanguageCodeHL(
        string $code
    ): ?LanguageModel {
        return $this->languageModelFactory->findOneByLanguageCodeHL($code);
    }

    /**
     * Retrieves ethnic names for a language by its ISO code.
     */
    public function getEthnicNamesForLanguageIso(
        string $languageCodeIso
    ): ?array {
        $query = 'SELECT ethnicName FROM hl_languages WHERE languageCodeIso = '
               . ':languageCodeIso';
        return $this->databaseService->fetchColumn(
            $query, 
            [':languageCodeIso' => $languageCodeIso]
        );
    }

    public function getLanguageCodes($languageCodeIso) {
        $query = 'SELECT languageCodeHL, languageCodeBibleBrain FROM hl_languages 
            WHERE languageCodeIso = :languageCodeIso LIMIT 1';
        return $this->databaseService->fetchRow($query, [':languageCodeIso' => $languageCodeIso]);
    }

    /**
     * Inserts a new language into the database.
     */
    public function insertLanguage(
        string $languageCodeIso,
        string $name
    ): void {
        $languageCodeHL = $languageCodeIso . date('y');
        $query = 'INSERT INTO hl_languages (languageCodeIso, languageCodeHL, 
                    name) VALUES (:languageCodeIso, :languageCodeHL, :name)';
        $params = [
            ':languageCodeIso' => $languageCodeIso,
            ':languageCodeHL' => $languageCodeHL,
            ':name' => $name
        ];
        $this->databaseService->executeQuery($query, $params);
    }

    /**
     * Updates the ethnic name of a language by ISO code.
     */
    public function updateEthnicName(
        string $languageCodeIso,
        string $ethnicName
    ): void {
        $query = 'UPDATE hl_languages SET ethnicName = :ethnicName WHERE '
               . 'languageCodeIso = :languageCodeIso';
        $params = [':ethnicName' => $ethnicName, ':languageCodeIso' => $languageCodeIso];
        $this->databaseService->executeQuery($query, $params);
    }

    /**
     * Updates the BibleBrain code for a language by ISO code.
     */
    public function updateLanguageCodeBibleBrain(
        string $languageCodeIso,
        string $languageCodeBibleBrain
    ): void {
        $query = 'UPDATE hl_languages SET languageCodeBibleBrain = '
               . ':languageCodeBibleBrain WHERE languageCodeIso = '
               . ':languageCodeIso';
        $params = [
            ':languageCodeBibleBrain' => $languageCodeBibleBrain,
            ':languageCodeIso' => $languageCodeIso
        ];
        $this->databaseService->executeQuery($query, $params);
    }
}
