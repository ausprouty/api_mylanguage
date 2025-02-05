<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Models\Language\DbsLanguageModel;

class DbsLanguageRepository extends BaseRepository
{

    public function __construct(DatabaseService $databaseService)
    {
        parent::__construct($databaseService);
    }

    /**
     * Save the DbsLanguageModel to the database.
     * Inserts a new record if it doesn't exist; updates it if it does.
     *
     * @param DbsLanguageModel $dbsLanguage
     */
    public function save(DbsLanguageModel $dbsLanguage)
    {
        if ($this->recordExists($dbsLanguage->getLanguageCodeHL())) {
            $this->updateRecord($dbsLanguage);
        } else {
            $this->insertRecord($dbsLanguage);
        }
    }

    /**
     * Checks if a record exists by languageCodeHL.
     *
     * @param string $languageCodeHL
     * @return bool
     */
    private function recordExists(string $languageCodeHL): bool
    {
        $query = "SELECT languageCodeHL FROM dbs_languages WHERE languageCodeHL = :code LIMIT 1";
        $params = [':code' => $languageCodeHL];
        return (bool) $this->databaseService->fetchSingleValue($query, $params);
    }

    /**
     * Inserts a new record into dbs_languages.
     *
     * @param DbsLanguageModel $dbsLanguage
     */
    private function insertRecord(DbsLanguageModel $dbsLanguage): void
    {
        $query = "INSERT INTO dbs_languages (languageCodeHL, collectionCode, format)
                  VALUES (:languageCodeHL, :collectionCode, :format)";
        $params = [
            ':languageCodeHL' => $dbsLanguage->getLanguageCodeHL(),
            ':collectionCode' => $dbsLanguage->getCollectionCode(),
            ':format' => $dbsLanguage->getFormat()
        ];
        $this->databaseService->executeQuery($query, $params);
    }

    /**
     * Updates an existing record in dbs_languages.
     *
     * @param DbsLanguageModel $dbsLanguage
     */
    private function updateRecord(DbsLanguageModel $dbsLanguage): void
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
        $this->databaseService->executeQuery($query, $params);
    }

    public function getLanguagesWithCompleteBible(){
        $query = "SELECT * FROM dbs_languages  as d
            INNER JOIN hl_languages as h
            ON d.languageCodeHL = h.languageCodeHL
            WHERE d.collectionCode = :collectionCode";
        $params = [':collectionCode' =>'C'];
        $result = $this->databaseService->fetchAll($query,$params);
        return $result;
    }
}
