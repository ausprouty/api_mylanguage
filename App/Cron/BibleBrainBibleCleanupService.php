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
        $offset = 0;
        $updatedCount = 0;

        do {
            $batch = $this->repository->getBiblesForCleanup($this->batchSize, $offset);
            if (empty($batch)) {
                break;
            }

            foreach ($batch as $bible) {
                $updated = $this->processBible($bible);
                if ($updated) {
                    $updatedCount++;
                }
            }

            $offset += $this->batchSize;
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
        $url = "bibles/defaults/types?language_code=$iso";

        $connection = new BibleBrainConnectionService($url);
        $bibleData = $connection->response->data ?? [];

        foreach ($bibleData as $entry) {
            foreach ($entry['filesets']['dbp-prod'] ?? [] as $fileset) {
                if (!str_starts_with($fileset['type'], 'text_')) {
                    continue;
                }

                // Match based on languageCodeHL and volume similarity
                if (
                    strtolower($bible['languageCodeHL']) === strtolower($bible['languageCodeHL']) &&
                    stripos($fileset['volume'], $bible['volumeName']) !== false
                ) {
                    if ($bible['externalId'] !== $fileset['id']) {
                        $this->repository->updateExternalId($bible['bid'], $fileset['id']);
                        $this->repository->markAsVerified($bible['bid']);
                        LoggerService::logInfo('BibleBrainCleanup', "Updated BID {$bible['bid']} from {$bible['externalId']} to {$fileset['id']}");
                        return true;
                    }
                }
            }
        }

        // Still mark as verified, even if no update needed
        $this->repository->markAsVerified($bible['bid']);
        return false;
    }
}
