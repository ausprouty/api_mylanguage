<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Models\Language\DbsLanguageModel;
use PDO;
use Exception;

class DbsLanguageRepository {
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function save(DbsLanguageModel $dbsLanguage)
    {
        // Check if the record exists
        if ($this->recordExists($dbsLanguage->getLanguageCodeHL())) {
            $this->updateRecord($dbsLanguage);
        } else {
            $this->insertRecord($dbsLanguage);
        }
    }

    private function recordExists($languageCodeHL)
    {
        $query = "SELECT languageCodeHL FROM dbs_languages WHERE languageCodeHL = :code LIMIT 1";
        $params = [':code' => $languageCodeHL];
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            return $results->fetch(PDO::FETCH_COLUMN) !== false;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return false;
        }
    }

    private function insertRecord(DbsLanguageModel $dbsLanguage)
    {
        $query = "INSERT INTO dbs_languages (languageCodeHL, collectionCode, format)
                  VALUES (:languageCodeHL, :collectionCode, :format)";
        $params = [
            ':languageCodeHL' => $dbsLanguage->getLanguageCodeHL(),
            ':collectionCode' => $dbsLanguage->getCollectionCode(),
            ':format' => $dbsLanguage->getFormat()
        ];
        try {
            $this->databaseService->executeQuery($query, $params);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }

    private function updateRecord(DbsLanguageModel $dbsLanguage)
    {
        $query = "UPDATE dbs_languages
                  SET collectionCode = :collectionCode, format = :format
                  WHERE languageCodeHL = :languageCodeHL
                  LIMIT 1";
        $params = [
            ':collectionCode' => $dbsLanguage->getCollectionCode(),
            ':format' => $dbsLanguage->getFormat(),
            ':languageCodeHL' => $dbsLanguage->getLanguageCodeHL()
        ];
        try {
            $this->databaseService->executeQuery($query, $params);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
    }
}
