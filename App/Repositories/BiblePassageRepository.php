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

    public function savePassageRecord(BiblePassageModel $biblePassage): void
    {
        if ($this->existsById($biblePassage->bpid)) {
            $this->updatePassageRecord($biblePassage);
        } else {
            $this->insertPassageRecord($biblePassage);
        }
    }

    private function existsById(string $bpid): bool
    {
        $query = 'SELECT bpid FROM bible_passages WHERE bpid = :bpid LIMIT 1';
        $params = [':bpid' => $bpid];
        $results = $this->databaseService->executeQuery($query, $params);
        return (bool) $results->fetch(PDO::FETCH_OBJ);
    }

    private function insertPassageRecord(BiblePassageModel $biblePassage): void
    {
        $query = 'INSERT INTO bible_passages (bpid, referenceLocalLanguage, passageText, passageUrl, dateLastUsed, timesUsed)
                  VALUES (:bpid, :referenceLocalLanguage, :passageText, :passageUrl, :dateLastUsed, :timesUsed)';
        $params = [
            ':bpid' => $biblePassage->bpid,
            ':referenceLocalLanguage' => $biblePassage->getReferenceLocalLanguage(),
            ':passageText' => $biblePassage->getPassageText(),
            ':passageUrl' => $biblePassage->getPassageUrl(),
            ':dateLastUsed' => date("Y-m-d"),
            ':timesUsed' => $biblePassage->timesUsed
        ];

        $this->databaseService->executeQuery($query, $params);
    }

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
