<?php

namespace App\Repositories;

use App\Models\Bible\BiblePassageModel;
use App\Services\Database\DatabaseService;
use PDO;

/**
 * Repository for handling Bible passage records in the database.
 */
class BiblePassageRepository
{
    /**
     * @var DatabaseService The service for interacting with the database.
     */
    private $databaseService;

    /**
     * Constructor to initialize the repository with a database service.
     *
     * @param DatabaseService $databaseService The database service instance.
     */
    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Checks if a Bible passage exists by its ID.
     *
     * @param string $bpid The ID of the Bible passage.
     * @return bool True if the passage exists, false otherwise.
     */
    private function existsById(string $bpid): bool
    {
        $query = 'SELECT bpid FROM bible_passages WHERE bpid = :bpid LIMIT 1';
        $params = [':bpid' => $bpid];
        $results = $this->databaseService->executeQuery($query, $params);
        return (bool) $results->fetch(PDO::FETCH_OBJ);
    }

    /**
     * Finds a stored Bible passage by its ID.
     *
     * @param string $bpid The ID of the Bible passage.
     * @return BiblePassageModel|null The Bible passage, or null if not found.
     */
    public function findStoredById(string $bpid): ?BiblePassageModel
    {
        $query = 'SELECT * FROM bible_passages WHERE bpid = :bpid LIMIT 1';
        $params = [':bpid' => $bpid];

        try {
            $results = $this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_OBJ);

            if ($data) {
                $biblePassage = new BiblePassageModel();
                $biblePassage->populateFromData($data);
                $this->updatePassageUse($biblePassage);
                return $biblePassage;
            }
        } catch (\Exception $e) {
            error_log("Error fetching Bible passage: " . $e->getMessage());
        }

        return null;
    }

    /**
     * Inserts a new Bible passage record into the database.
     *
     * @param BiblePassageModel $biblePassage The Bible passage to insert.
     */
    private function insertPassageRecord(BiblePassageModel $biblePassage): void
    {
        $query = 'INSERT INTO bible_passages 
                  (bpid, referenceLocalLanguage, passageText, passageUrl, 
                   dateLastUsed, timesUsed)
                  VALUES 
                  (:bpid, :referenceLocalLanguage, :passageText, :passageUrl, 
                   :dateLastUsed, :timesUsed)';
        $params = [
            ':bpid' => $biblePassage->bpid,
            ':referenceLocalLanguage' => $biblePassage->getReferenceLocalLanguage(),
            ':passageText' => $biblePassage->getPassageText(),
            ':passageUrl' => $biblePassage->getPassageUrl(),
            ':dateLastUsed' => date("Y-m-d"),
            ':timesUsed' => $biblePassage->getTimesUsed()
        ];

        $this->databaseService->executeQuery($query, $params);
    }

    /**
     * Saves a Bible passage record, updating it if it already exists.
     *
     * @param BiblePassageModel $biblePassage The Bible passage to save.
     */
    public function savePassageRecord(BiblePassageModel $biblePassage): void
    {
        if ($this->existsById($biblePassage->bpid)) {
            $this->updatePassageRecord($biblePassage);
        } else {
            $this->insertPassageRecord($biblePassage);
        }
    }

    /**
     * Updates an existing Bible passage record in the database.
     *
     * @param BiblePassageModel $biblePassage The Bible passage to update.
     */
    private function updatePassageRecord(BiblePassageModel $biblePassage): void
    {
        $query = 'UPDATE bible_passages
                  SET referenceLocalLanguage = :referenceLocalLanguage, 
                      passageText = :passageText, 
                      passageUrl = :passageUrl
                  WHERE bpid = :bpid LIMIT 1';
        $params = [
            ':referenceLocalLanguage' => $biblePassage->getReferenceLocalLanguage(),
            ':passageText' => $biblePassage->getPassageText(),
            ':passageUrl' => $biblePassage->getPassageUrl(),
            ':bpid' => $biblePassage->bpid
        ];

        $this->databaseService->executeQuery($query, $params);
    }

    /**
     * Updates the usage statistics for a Bible passage.
     *
     * @param BiblePassageModel $biblePassage The Bible passage to update.
     */
    private function updatePassageUse(BiblePassageModel $biblePassage): void
    {
        $biblePassage->updateUsage();
        $query = 'UPDATE bible_passages
                  SET dateLastUsed = :dateLastUsed, timesUsed = :timesUsed
                  WHERE bpid = :bpid LIMIT 1';
        $params = [
            ':dateLastUsed' => $biblePassage->dateLastUsed,
            ':timesUsed' => $biblePassage->timesUsed,
            ':bpid' => $biblePassage->bpid
        ];

        $this->databaseService->executeQuery($query, $params);
    }
}
