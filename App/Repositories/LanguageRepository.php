<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Models\Language\LanguageModel;
use PDO;
use Exception;

class LanguageRepository {
    private $databaseService;

    public function __construct(DatabaseService $databaseService) {
        $this->databaseService = $databaseService;
    }

    public function bibleBrainLanguageRecordExists($languageCodeBibleBrain) {
        $query = 'SELECT id FROM hl_languages WHERE languageCodeBibleBrain = :languageCodeBibleBrain LIMIT 1';
        $params = [':languageCodeBibleBrain' => $languageCodeBibleBrain];
        return $this->fetchColumn($query, $params) !== false;
    }

    public function clearCheckedBBBibles() {
        $query = 'UPDATE hl_languages SET CheckedBBBibles = NULL';
        $this->databaseService->executeQuery($query);
    }

    public function createLanguageFromBibleBrainRecord(LanguageModel $language) {
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

    public function ethnicNamesForLanguageIso($languageCodeIso) {
        $query = 'SELECT ethnicName FROM hl_languages WHERE languageCodeIso = :languageCodeIso';
        return $this->fetchAll($query, [':languageCodeIso' => $languageCodeIso], PDO::FETCH_COLUMN);
    }

    public function findOneByCode(string $source, string $code) {
        $field = 'languageCode' . $source;
        $query = 'SELECT * FROM hl_languages WHERE ' . $field . ' = :id';
        return $this->fetchSingle($query, [':id' => $code]);
    }

    public function findOneByLanguageCodeHL($languageCodeHL) {
        return $this->findOneByCode('HL', $languageCodeHL);
    }

    public function getCodeIsoFromCodeHL($languageCodeHL) {
        $query = "SELECT languageCodeIso FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1";
        return $this->fetchColumn($query, [':languageCodeHL' => $languageCodeHL]);
    }

    public function getEnglishNameFromCodeHL($languageCodeHL) {
        $query = "SELECT name FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1";
        return $this->fetchColumn($query, [':languageCodeHL' => $languageCodeHL]);
    }

    public function getEthnicNamesForLanguageIso($languageCodeIso) {
        $query = 'SELECT ethnicName FROM hl_languages WHERE languageCodeIso = :languageCodeIso';
        return $this->fetchAll($query, [':languageCodeIso' => $languageCodeIso], PDO::FETCH_COLUMN);
    }

    public function getFontDataFromCodeHL($languageCodeHL) {
        $query = "SELECT fontData FROM hl_languages WHERE languageCodeHL = :languageCodeHL LIMIT 1";
        $data = $this->fetchColumn($query, [':languageCodeHL' => $languageCodeHL]);
        return $data ? json_decode($data, true) : null;
    }

    public function getLanguageCodes($languageCodeIso) {
        $query = 'SELECT languageCodeHL, languageCodeBibleBrain FROM hl_languages WHERE languageCodeIso = :languageCodeIso LIMIT 1';
        return $this->fetchSingle($query, [':languageCodeIso' => $languageCodeIso]);
    }

    public function getNextLanguageForLanguageDetails() {
        $query = "SELECT languageCodeIso FROM hl_languages WHERE languageCodeBibleBrain IS NULL AND checkedBBBibles IS NOT NULL LIMIT 1";
        return $this->fetchColumn($query);
    }

    public function insertLanguage($languageCodeIso, $name) {
        $languageCodeHL = $languageCodeIso . date('y');
        $query = 'INSERT INTO hl_languages (languageCodeIso, languageCodeHL, name) VALUES (:languageCodeIso, :languageCodeHL, :name)';
        $params = [
            ':languageCodeIso' => $languageCodeIso,
            ':languageCodeHL' => $languageCodeHL,
            ':name' => $name
        ];
        $this->databaseService->executeQuery($query, $params);
    }

    public function languageIsoRecordExists($languageCodeIso) {
        $query = 'SELECT id FROM hl_languages WHERE languageCodeIso = :languageCodeIso LIMIT 1';
        return $this->fetchColumn($query, [':languageCodeIso' => $languageCodeIso]);
    }

    public function setLanguageDetailsComplete($languageCodeIso) {
        $query = "UPDATE hl_languages SET checkedBBBibles = NULL WHERE languageCodeIso = :languageCodeIso LIMIT 1";
        $params = [':languageCodeIso' => $languageCodeIso];
        $this->databaseService->executeQuery($query, $params);
    }

    public function updateEthnicName($languageCodeIso, $ethnicName) {
        $query = "UPDATE hl_languages SET ethnicName = :ethnicName WHERE languageCodeIso = :languageCodeIso";
        $params = [
            ':ethnicName' => $ethnicName,
            ':languageCodeIso' => $languageCodeIso
        ];
        $this->databaseService->executeQuery($query, $params);
    }

    public function updateLanguageCodeBibleBrain($languageCodeIso, $languageCodeBibleBrain) {
        $query = "UPDATE hl_languages SET languageCodeBibleBrain = :languageCodeBibleBrain WHERE languageCodeIso = :languageCodeIso";
        $params = [
            ':languageCodeBibleBrain' => $languageCodeBibleBrain,
            ':languageCodeIso' => $languageCodeIso
        ];
        $this->databaseService->executeQuery($query, $params);
    }

    // Utility methods
    private function fetchAll($query, $params, $fetchStyle) {
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            return $results->fetchAll($fetchStyle);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return [];
        }
    }

    private function fetchColumn($query, $params) {
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            return $results->fetch(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    private function fetchSingle($query, $params) {
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            return $results->fetch(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}
