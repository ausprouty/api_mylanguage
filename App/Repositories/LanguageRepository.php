<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Models\Language\LanguageModel;

class LanguageRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function bibleBrainLanguageRecordExists($languageCodeBibleBrain): bool
    {
        $query = 'SELECT id FROM hl_languages WHERE languageCodeBibleBrain = :languageCodeBibleBrain LIMIT 1';
        $params = [':languageCodeBibleBrain' => $languageCodeBibleBrain];
        return $this->databaseService->fetchSingleValue($query, $params) !== null;
    }

    public function clearCheckedBBBibles(): void
    {
        $query = 'UPDATE hl_languages SET CheckedBBBibles = NULL';
        $this->databaseService->executeQuery($query);
    }

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

    public function getEthnicNamesForLanguageIso($languageCodeIso): ?array
    {
        $query = 'SELECT ethnicName FROM hl_languages WHERE languageCodeIso = :languageCodeIso';
        return $this->databaseService->fetchColumn($query, [':languageCodeIso' => $languageCodeIso]);
    }

    public function findOneByCode(string $source, string $code): ?LanguageModel
    {
        $field = 'languageCode' . $source;
        $query = 'SELECT * FROM hl_languages WHERE ' . $field . ' = :id';
        $data = $this->databaseService->fetchRow($query, [':id' => $code]);
        return $data ? new LanguageModel($data) : null;
    }

    public function getCodeIsoFromCodeHL($languageCodeHL): ?string
    {
        $query = "SELECT languageCodeIso FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1";
        return $this->databaseService->fetchSingleValue($query, [':languageCodeHL' => $languageCodeHL]);
    }

    public function getEnglishNameFromCodeHL($languageCodeHL): ?string
    {
        $query = "SELECT name FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1";
        return $this->databaseService->fetchSingleValue($query, [':languageCodeHL' => $languageCodeHL]);
    }

    public function getFontDataFromCodeHL($languageCodeHL): ?array
    {
        $query = "SELECT fontData FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1";
        $data = $this->databaseService->fetchSingleValue($query, [':languageCodeHL' => $languageCodeHL]);
        return $data ? json_decode($data, true) : null;
    }

    public function getLanguageCodes($languageCodeIso): ?array
    {
        $query = 'SELECT languageCodeHL, languageCodeBibleBrain FROM hl_languages WHERE languageCodeIso = :languageCodeIso LIMIT 1';
        return $this->databaseService->fetchRow($query, [':languageCodeIso' => $languageCodeIso]);
    }

    public function getNextLanguageForLanguageDetails(): ?string
    {
        $query = "SELECT languageCodeIso FROM hl_languages WHERE languageCodeBibleBrain IS NULL AND checkedBBBibles IS NOT NULL LIMIT 1";
        return $this->databaseService->fetchSingleValue($query);
    }

    public function insertLanguage($languageCodeIso, $name): void
    {
        $languageCodeHL = $languageCodeIso . date('y');
        $query = 'INSERT INTO hl_languages (languageCodeIso, languageCodeHL, name) VALUES (:languageCodeIso, :languageCodeHL, :name)';
        $params = [
            ':languageCodeIso' => $languageCodeIso,
            ':languageCodeHL' => $languageCodeHL,
            ':name' => $name
        ];
        $this->databaseService->executeQuery($query, $params);
    }

    public function languageIsoRecordExists($languageCodeIso): bool
    {
        $query = 'SELECT id FROM hl_languages WHERE languageCodeIso = :languageCodeIso LIMIT 1';
        return $this->databaseService->fetchSingleValue($query, [':languageCodeIso' => $languageCodeIso]) !== null;
    }

    public function setLanguageDetailsComplete($languageCodeIso): void
    {
        $query = "UPDATE hl_languages SET checkedBBBibles = NULL WHERE languageCodeIso = :languageCodeIso LIMIT 1";
        $this->databaseService->executeQuery($query, [':languageCodeIso' => $languageCodeIso]);
    }

    public function updateEthnicName($languageCodeIso, $ethnicName): void
    {
        $query = "UPDATE hl_languages SET ethnicName = :ethnicName WHERE languageCodeIso = :languageCodeIso";
        $this->databaseService->executeQuery($query, [
            ':ethnicName' => $ethnicName,
            ':languageCodeIso' => $languageCodeIso
        ]);
    }

    public function updateLanguageCodeBibleBrain($languageCodeIso, $languageCodeBibleBrain): void
    {
        $query = "UPDATE hl_languages SET languageCodeBibleBrain = :languageCodeBibleBrain WHERE languageCodeIso = :languageCodeIso";
        $this->databaseService->executeQuery($query, [
            ':languageCodeBibleBrain' => $languageCodeBibleBrain,
            ':languageCodeIso' => $languageCodeIso
        ]);
    }
}
