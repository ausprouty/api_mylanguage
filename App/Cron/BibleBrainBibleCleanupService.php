<?php

namespace App\Cron;

use App\Repositories\BibleBrainBibleRepository;
use App\Services\Web\BibleBrainConnectionService;
use App\Services\LoggerService;

/**
 * BibleBrainBibleCleanupService
 *
 * One-time script to clean and update the `externalId` values
 * in the local `bibles` table by comparing with the latest BibleBrain filesets.
 *
 * Only updates records with `source = 'dbt'` and `format = 'text'`.
 * This script should be run manually or via a one-off cron to ensure
 * all legacy externalId entries are brought up to date.
 */
class BibleBrainBibleCleanupService
{
    private BibleBrainBibleRepository $repository;
    private string $logFile;
    private int $batchSize = 100;

    public function __construct(BibleBrainBibleRepository $repository)
    {
        $this->repository = $repository;
        $this->logFile = __DIR__ . '/../../data/cron/last_biblebrain_cleanup.txt';
    }

    /**

    * Runs cleanup on outdated externalId values in the local bibles table.
    */
    public function run(): void
    {
        $lastBid = 0;
        $updatedCount = 0;

        do {
            $batch = $this->repository->getBiblesForCleanup($this->batchSize, $lastBid);
           // LoggerService::logInfo('BibleBrainCleanup-42', $batch);
            if (empty($batch)) {
               // LoggerService::logInfo('BibleBrainCleanup-44', 'batch is empty');
                break;
            }
            foreach ($batch as $bible) {
                //LoggerService::logInfo('BibleBrainCleanup-46', $bible);
                $updated = $this->processBible($bible);
                if ($updated) {
                    $updatedCount++;
                }
                $lastBid = max($lastBid, $bible['bid']);
            }
        } while (count($batch) === $this->batchSize);

        LoggerService::logInfo('BibleBrainCleanup', "Total updated: $updatedCount");
        file_put_contents($this->logFile, date('Y-m-d'));
    }

    /**
     * Process a single Bible row by checking BibleBrain filesets and updating externalId if needed.
     */
    private function processBible(array $bible): bool
    {
        $iso = strtoupper($bible['languageCodeIso']);
        $url = "bibles?language_code=$iso";
        $connection = new BibleBrainConnectionService($url);
        $bibleData = $connection->response['data'] ?? [];
        foreach ($bibleData as $entry) {
            foreach ($entry['filesets']['dbp-prod'] ?? [] as $fileset) {
                if ($this->isMatchingBible($bible, $fileset)) {
                    $this->repository->updateExternalId($bible['bid'], $fileset['id']);
                    $this->repository->updateDateVerified($bible['bid']);
                    LoggerService::logInfo('BibleBrainCleanup', "Matched and updated {$bible['bid']}");
                    return true;
                }
            }
        }
        return false;
    }

    /**
    *  ISO language code must match exactly:
    *      bible.languageCodeIso === fileset.iso
    *  Prefix of externalId matches fileset ID (e.g., first 7–9 characters):
    *      substr(bible.externalId, 0, 7) === substr(fileset['id'], 0, 7)
    *   Collection code matches:
    *       bible.collectionCode === fileset['size']
    *  Format starts with 'text_' (already filtered)
    *  Optional fallback volume name similarity — if needed, use fuzzy match or just log the discrepancy for review.
     */
    private array $formatAliases = [
        'text' => ['text', 'text_plain'],
    ];

    private function isMatchingBible(array $bible, array $fileset): bool
    {
        if (!isset($fileset['type'], $fileset['id'], $fileset['size'])) {
            return false;
        }

        if (!str_starts_with($fileset['type'], 'text')) {
            return false;
        }

        $externalPrefix = substr($bible['externalId'], 0, 7);
        $filesetPrefix  = substr($fileset['id'], 0, 7);

        // check if format matches directly or via alias
        $acceptableFormats = $this->formatAliases[$bible['format']] ?? [$bible['format']];
        $formatMatches = in_array($fileset['type'], $acceptableFormats, true);

        return (
            $externalPrefix === $filesetPrefix &&
            $bible['collectionCode'] === $fileset['size'] &&
            $formatMatches
        );
    }
}
