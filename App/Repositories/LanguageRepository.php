<?php

namespace App\Repositories;

use App\Factories\LanguageFactory;
use App\Models\Language\LanguageModel;
use App\Services\Database\DatabaseService;

/**
 * Handles database operations for the LanguageModel.
 */
class LanguageRepository extends BaseRepository
{

    private $languageFactory;

    /**
     * Constructor to initialize dependencies.
     */
    public function __construct(
        DatabaseService $databaseService,
        LanguageFactory $languageFactory
    ) {
        parent::__construct($databaseService); // Call the parent constructor inside the body
        $this->languageFactory = $languageFactory; // Initialize other properties
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
     * Finds a language by a specific source code using the base repository method.
     */
    public function findOneByCode(
        string $source,
        string $code
    ): ?LanguageModel {
        $field = 'languageCode' . $source;
        $query = 'SELECT * FROM hl_languages WHERE ' . $field . ' = :id';
        return $this->fetchAndPopulateModel(
            $query,
            [':id' => $code],
            LanguageModel::class
        );
    }

    /**
     * Finds a LanguageModel by its HL code.
     */
    public function findOneLanguageByLanguageCodeHL(
        string $code
    ): ?LanguageModel {
        $query = 'SELECT * FROM hl_languages WHERE languageCodeHL = :id';
        return $this->fetchAndPopulateModel(
            $query,
            [':id' => $code],
            LanguageModel::class
        );
    }


    /**
     * Finds a LanguageModel by a specific source code.
     */

    /**
     * Retrieves ISO language code from HL language code.
     */
    public function getCodeIsoFromCodeHL(string $languageCodeHL): ?string
{
    $query = 'SELECT languageCodeIso FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1';
    return $this->databaseService->fetchSingleValue($query, [
        ':languageCodeHL' => $languageCodeHL
    ]);
}


    /**
     * Retrieves Google language code from HL language code.
     */
    public function getCodeGoogleFromCodeHL(string $languageCodeHL): ?string
{
    $query = 'SELECT languageCodeGoogle FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1';
    return $this->databaseService->fetchSingleValue($query, [
        ':languageCodeHL' => $languageCodeHL
    ]);
}


    /**
     * Retrieves English name for a language by ISO code.
     */
    public function getEnglishNameForLanguageCodeIso(
        string $languageCodeIso
    ): ?string {
        $query = 'SELECT name FROM hl_languages WHERE languageCodeIso = '
            . ':languageCodeIso';
        return  $this->databaseService->fetchSingleValue(
            $query,
            [':languageCodeIso' => $languageCodeIso]
        );

    }

    /**
     * Retrieves English name for a language by HL code.
     */
    public function getEnglishNameForLanguageCodeHL(
        string $languageCodeHL
    ): ?string {

        $query = 'SELECT name FROM hl_languages WHERE languageCodeHL = '
            . ':languageCodeHL';
        return  $this->databaseService->fetchSingleValue(
            $query,
            [':languageCodeHL' => $languageCodeHL]
        );
    }

    /**
     * Retrieves ethnic name for a language by ISO code.
     */
    public function getEthnicNameForLanguageCodeIso(string $languageCodeIso): ?string
{
    $query = 'SELECT ethnicName FROM hl_languages WHERE languageCodeIso = :languageCodeIso LIMIT 1';

    return $this->databaseService->fetchSingleValue($query, [
        ':languageCodeIso' => $languageCodeIso
    ]);

}


    /**
     * Retrieves font data for a language by HL code.
     */
    public function getFontDataFromLanguageCodeHL(string $languageCodeHL): ?string
{
    $query = 'SELECT fontData FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1';

    return $this->databaseService->fetchSingleValue($query, [
        ':languageCodeHL' => $languageCodeHL
    ]);

}


    /**
     * Retrieves language codes for a given ISO code.
     */
    public function getLanguageCodes(string $languageCodeIso): ?array
    {
        $query = 'SELECT languageCodeHL, languageCodeBibleBrain FROM '
            . 'hl_languages WHERE languageCodeIso = :languageCodeIso LIMIT 1';
        return $this->databaseService->fetchRow(
            $query,
            [':languageCodeIso' => $languageCodeIso]
        );
    }

    /**
     * Retrieves the next language for language details processing.
     */
    public function getNextLanguageForLanguageDetails(): ?string
    {
        $query = 'SELECT languageCodeIso FROM hl_languages WHERE '
            . 'languageCodeBibleBrain IS NULL AND CheckedBBBibles IS NOT NULL '
            . 'LIMIT 1';
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

    /**
     * Checks if an ISO language record exists.
     */
    public function languageIsoRecordExists(string $languageCodeIso): bool
    {
        $query = 'SELECT id FROM hl_languages WHERE languageCodeIso = '
            . ':languageCodeIso LIMIT 1';
        return $this->databaseService->fetchColumn(
            $query,
            [':languageCodeIso' => $languageCodeIso]
        ) !== null;
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
        $params = [
            ':ethnicName' => $ethnicName,
            ':languageCodeIso' => $languageCodeIso
        ];
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
