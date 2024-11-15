<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Models\Bible\BiblePassageModel;
use PDO;

class BiblePassageRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    // Find a Bible passage by its ID and populate the model
    public function findStoredById($bpid): ?BiblePassageModel
    {
        $query = 'SELECT * FROM bible_passages WHERE bpid = :bpid LIMIT 1';
        $params = [':bpid' => $bpid];
        
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_OBJ);

            if ($data) {
                $biblePassage = new BiblePassageModel();
                $biblePassage->populateFromData($data);
                // Update usage stats upon retrieval
                $this->updatePassageUse($biblePassage); 
                return $biblePassage;
            }
        } catch (\Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }

        return null;
    }

    // Insert a new Bible passage record
    public function insertPassageRecord(BiblePassageModel $biblePassage)
    {
        $query = 'INSERT INTO bible_passages 
                  (bpid, referenceLocalLanguage, passageText, 
                   passageUrl, dateLastUsed, dateChecked, timesUsed)
                  VALUES (:bpid, :referenceLocalLanguage, :passageText, 
                          :passageUrl, :dateLastUsed, :dateChecked, 
                          :timesUsed)';
        $params = [
            ':bpid' => $biblePassage->bpid,
            ':referenceLocalLanguage' => $biblePassage->referenceLocalLanguage,
            ':passageText' => $biblePassage->passageText,
            ':passageUrl' => $biblePassage->passageUrl,
            ':dateLastUsed' => date("Y-m-d"),
            ':dateChecked' => null,
            ':timesUsed' => 1
        ];

        $this->databaseService->executeQuery($query, $params);
    }

    // Save a passage record, updating if it exists, or inserting if not
    public function savePassageRecord(BiblePassageModel $biblePassage)
    {
        if ($this->existsById($biblePassage->bpid)) {
            $this->updatePassageRecord($biblePassage);
        } else {
            $this->insertPassageRecord($biblePassage);
        }
    }

    // Check if a passage record exists
    private function existsById($bpid): bool
    {
        $query = 'SELECT bpid FROM bible_passages WHERE bpid = :bpid LIMIT 1';
        $params = [':bpid' => $bpid];
        $results = $this->databaseService->executeQuery($query, $params);
        return $results->fetch(PDO::FETCH_OBJ) ? true : false;
    }

    // Update an existing passage record
    private function updatePassageRecord(BiblePassageModel $biblePassage)
    {
        $query = 'UPDATE bible_passages
                  SET referenceLocalLanguage = :referenceLocalLanguage,
                      passageText = :passageText,
                      passageUrl = :passageUrl
                  WHERE bpid = :bpid LIMIT 1';
        $params = [
            ':referenceLocalLanguage' => $biblePassage->referenceLocalLanguage,
            ':passageText' => $biblePassage->passageText,
            ':passageUrl' => $biblePassage->passageUrl,
            ':bpid' => $biblePassage->bpid
        ];

        $this->databaseService->executeQuery($query, $params);
    }

    // Update usage stats for a passage
    private function updatePassageUse(BiblePassageModel $biblePassage)
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

    // Update the dateChecked field
    public function updateDateChecked($bpid)
    {
        $query = 'UPDATE bible_passages 
                  SET dateChecked = :today 
                  WHERE bpid = :bpid LIMIT 1';
        $params = [
            ':today' => date("Y-m-d"),
            ':bpid' => $bpid
        ];
        $this->databaseService->executeQuery($query, $params);
    }

    // Update the passage URL field
    public function updatePassageUrl($bpid, $url)
    {
        $query = 'UPDATE bible_passages 
                  SET passageUrl = :passageUrl 
                  WHERE bpid = :bpid LIMIT 1';
        $params = [
            ':passageUrl' => $url,
            ':bpid' => $bpid
        ];
        $this->databaseService->executeQuery($query, $params);
    }
}
