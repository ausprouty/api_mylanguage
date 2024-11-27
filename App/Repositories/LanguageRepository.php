<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Factories\LanguageFactory;
use App\Models\Language\LanguageModel;

/**
 * Handles database operations for the LanguageModel.
 */
class LanguageRepository
{
    private $databaseService;
    private $languageFactory;

    /**
     * Constructor to initialize dependencies.
     */
    public function __construct(
        DatabaseService $databaseService,
        LanguageFactory $languageFactory
    ) {
        $this->databaseService = $databaseService;
        $this->languageFactory = $languageFactory;
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
        return $this->languageFactory->findOneByCode($source, $code);
    }

    /**
     * Finds a language by its HL code using the factory.
     */
    public function findOneLanguageByLanguageCodeHL(
        string $code
    ): ?LanguageModel {
        return $this->languageFactory->findOneLanguageByLanguageCodeHL($code);
    }

    public function getCodeIsoFromCodeHL($languageCodeHL)
    {
        $query = "SELECT languageCodeIso FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1";
        return $this->databaseService->fetchColumn($query, [':languageCodeHL' => $languageCodeHL]);
    }

    public function getEnglishNameFromCodeHL($languageCodeHL)
    {
        $query = "SELECT name FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1";
        return $this->databaseService->fetchColumn($query, [':languageCodeHL' => $languageCodeHL]);
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
    public function getFontDataFromCodeHL(string $languageCodeHL)
    {
        $query = "SELECT fontData FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1";
        $data = $this->databaseService->fetchColumn($query, [':languageCodeHL' => $languageCodeHL]);
        return $data ? $data : null;
    }

    public function getLanguageCodes(string $languageCodeIso)
    {
        $query = 'SELECT languageCodeHL, languageCodeBibleBrain FROM hl_languages 
            WHERE languageCodeIso = :languageCodeIso LIMIT 1';
        return $this->databaseService->fetchRow($query, [':languageCodeIso' => $languageCodeIso]);
    }
    public function getNextLanguageForLanguageDetails()
    {
        $query = "SELECT languageCodeIso FROM hl_languages WHERE languageCodeBibleBrain IS NULL AND checkedBBBibles IS NOT NULL LIMIT 1";
        return $this->databaseService->fetchColumn($query);
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

    public function languageIsoRecordExists(string $languageCodeIso)
    {
        $query = 'SELECT id FROM hl_languages 
            WHERE languageCodeIso = :languageCodeIso LIMIT 1';
        return $this->databaseService->fetchColumn(
            $query,
            [':languageCodeIso' => $languageCodeIso]
        );
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
