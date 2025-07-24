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
     * Updates the externalId for a Bible row.
     */
    public function updateExternalId(int $bid, string $newId): void
    {
        $query = 'UPDATE bibles SET externalId = :externalId, dateVerified = CURDATE() WHERE bid = :bid';
        $this->databaseService->executeQuery($query, [':externalId' => $newId, ':bid' => $bid]);
    }

    /**
     * Updates the dateVerified field to today's date for a Bible row.
     */
    public function updateDateVerified(int $bid): void
    {
        $query = 'UPDATE bibles SET dateVerified = CURDATE() WHERE bid = :bid';
        $this->databaseService->executeQuery($query, [':bid' => $bid]);
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
     * Only includes Bibles that have not yet been verified.
     */
    public function getBiblesForCleanup(int $limit, int $lastBid = 0): array
    {
        $query = '
            SELECT *
            FROM bibles
            WHERE source = :source
            AND format LIKE :formatPrefix
            AND (dateVerified IS NULL OR dateVerified = "0000-00-00")
            AND bid > :lastBid
            ORDER BY bid ASC
            LIMIT :limit
        ';
        $params = [
            ':source'       => 'dbt',
            ':formatPrefix' => 'text%',
            ':lastBid'      => $lastBid,
            ':limit'        => $limit,
        ];

        return $this->databaseService->fetchAll($query, $params);
    }

    public function updateLanguageFieldsIfMissing(string $externalId, array $entry): void
{
    $query = '
        UPDATE bibles
        SET languageEnglish = :languageEnglish,
            languageName = :languageAutonym,
            languageCodeBibleBrain = :languageCodeBibleBrain,
            dateVerified = :dateVerified
        WHERE externalId = :externalId
          AND (languageCodeBibleBrain IS NULL)
    ';
    $dateVerified = date('Y-m-d');
    $this->databaseService->executeQuery($query, [
        ':languageEnglish' => $entry['language'] ?? '',
        ':languageAutonym' => $entry['autonym'] ?? '',
        ':languageCodeBibleBrain' => $entry['language_id'] ?? '',
        ':dateVerified'=> $dateVerified, 
        ':externalId' => $externalId,
    ]);
}



}
