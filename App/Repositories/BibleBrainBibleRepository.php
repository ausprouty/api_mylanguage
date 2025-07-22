<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;

/**
 * BibleBrainBibleRepository
 *
 * Handles BibleBrain-related synchronization between BibleBrain API data
 * and local `hl_languages` and `bibles` tables.
 */
class BibleBrainBibleRepository extends BaseRepository
{
    public function __construct(DatabaseService $databaseService)
    {
        parent::__construct($databaseService);
    }

    /**
     * Fetches the next language needing BibleBrain verification.
     * Only languages with a valid BibleBrain code and unverified in the last 6 months.
     */
    public function getNextLanguageForBibleBrainSync(): ?array
    {
        $query = 'SELECT languageCodeHL, languageCodeIso, languageCodeBibleBrain
                  FROM hl_languages
                  WHERE languageCodeBibleBrain IS NOT NULL
                    AND (CheckedBBBibles IS NULL OR CheckedBBBibles < DATE_SUB(CURDATE(), INTERVAL 6 MONTH))
                  ORDER BY CheckedBBBibles ASC
                  LIMIT 1';

        return $this->databaseService->fetchRow($query);
    }

    /**
     * Updates CheckedBBBibles date to today for a given ISO code.
     */
    public function markLanguageAsChecked(string $languageCodeIso): void
    {
        $query = 'UPDATE hl_languages SET CheckedBBBibles = CURDATE() WHERE languageCodeIso = :iso';
        $this->databaseService->executeQuery($query, [':iso' => $languageCodeIso]);
    }

    /**
     * Updates the externalId for a Bible row.
     */
    public function updateExternalId(int $bid, string $newId): void
    {
        $query = 'UPDATE bibles SET externalId = :externalId, dateVerified = CURDATE() WHERE bid = :bid';
        $this->databaseService->executeQuery($query, [':externalId' => $newId, ':bid' => $bid]);
    }

    /**
     * Checks if a Bible record already exists by externalId.
     */
    public function bibleRecordExists(string $externalId): bool
    {
        $query = 'SELECT bid FROM bibles WHERE externalId = :externalId LIMIT 1';
        return $this->databaseService->fetchSingleValue($query, [':externalId' => $externalId]) !== null;
    }

    /**
     * Inserts a new Bible record into the `bibles` table.
     */
    public function insertBibleRecord(array $data): void
    {
        $columns = array_keys($data);
        $placeholders = array_map(fn($col) => ":$col", $columns);

        $query = 'INSERT INTO bibles (' . implode(',', $columns) . ') VALUES (' . implode(',', $placeholders) . ')';
        $this->databaseService->executeQuery($query, array_combine($placeholders, array_values($data)));
    }

    /**
     * Finds existing Bible record for matching language and volume name.
     */
    public function findMatchingBible(string $languageCodeIso, string $volumeName, string $format = 'text'): ?array
    {
        $query = 'SELECT * FROM bibles
                  WHERE languageCodeIso = :iso
                    AND format = :format
                    AND source = "dbt"
                    AND volumeName LIKE CONCAT("%", :volumeName, "%")
                  LIMIT 1';

        return $this->databaseService->fetchRow($query, [
            ':iso' => $languageCodeIso,
            ':format' => $format,
            ':volumeName' => $volumeName
        ]);
    }

    /**
     * Retrieves a batch of bibles for initial cleanup.
     */
    public function getBiblesForCleanup(int $limit, int $offset): array
    {
        $query = 'SELECT * FROM bibles
                  WHERE source = "dbt"
                    AND format = "text"
                  ORDER BY bid ASC
                  LIMIT :limit OFFSET :offset';

        return $this->databaseService->fetchAll($query, [
            ':limit' => $limit,
            ':offset' => $offset
        ]);
    }
}
