<?php

namespace App\Cron;

use App\Repositories\BibleBrainBibleRepository;
use App\Services\Web\BibleBrainConnectionService;
use App\Services\LoggerService;

/**
 * BibleBrainBibleSyncService
 *
 * Meant to be run once per month via cron. Adds new BibleBrain text filesets
 * to the local `bibles` table and marks any unchanged records as verified.
 */
class BibleBrainBibleSyncService
{
    private BibleBrainBibleRepository $repository;
    private string $logFile;
    private int $batchSize = 100;

    public function __construct(BibleBrainBibleRepository $repository)
    {
        $this->repository = $repository;
        $this->logFile = __DIR__ . '/../../data/cron/last_biblebrain_bible_sync.txt';
    }

    /**
     * Runs the sync if it hasn't already run this calendar month.
     */
    public function syncOncePerMonth(): void
    {
        if ($this->hasRunThisMonth()) {
            LoggerService::logInfo('BibleBrainSync', 'Sync already performed this month. Skipping.');
            return;
        }

        $this->syncNewBibles();
        $this->updateLastRunTimestamp();
        LoggerService::logInfo('BibleBrainSync', 'Sync completed and timestamp updated.');
    }

    /**
     * Syncs new BibleBrain text filesets into the local DB.
     */
    private function syncNewBibles(): void
    {
        $offset = 0;
        $addedCount = 0;

        do {
            $languages = $this->repository->getUnverifiedLanguages($this->batchSize, $offset);
            if (empty($languages)) {
                break;
            }

            foreach ($languages as $language) {
                $iso = strtoupper($language['languageCodeIso']);
                $url = "bibles/defaults/types?language_code=$iso";

                $connection = new BibleBrainConnectionService($url);
                $entries = $connection->response->data ?? [];

                foreach ($entries as $entry) {
                    $addedCount += $this->processEntry($entry, $language);
                }
            }

            $offset += $this->batchSize;
        } while (count($languages) === $this->batchSize);

        LoggerService::logInfo('BibleBrainSync', "Total new entries added: $addedCount");
    }

    /**
     * Processes a single Bible entry from BibleBrain and inserts any new filesets.
     */
    private function processEntry(array $entry, array $language): int
    {
        $added = 0;
        $filesets = $entry['filesets']['dbp-prod'] ?? [];

        foreach ($filesets as $fs) {
            if (!str_starts_with($fs['type'], 'text_')) {
                continue;
            }

            if (!$this->repository->bibleRecordExists($fs['id'])) {
                $this->repository->insertBibleRecord([
                    'externalId' => $fs['id'],
                    'volumeName' => $fs['volume'] ?? $entry['name'] ?? '',
                    'languageCodeIso' => $language['languageCodeIso'],
                    'languageCodeHL' => $language['languageCodeHL'],
                    'source' => 'dbt',
                    'format' => 'text',
                    'text' => 'Y',
                    'audio' => '',
                    'video' => '',
                    'dateVerified' => date('Y-m-d'),
                ]);
                LoggerService::logInfo('BibleBrainSync', "Inserted new Bible fileset: {$fs['id']}");
                $added++;
            }
        }

        $this->repository->markLanguageAsVerified($language['languageCodeIso']);
        return $added;
    }

    /**
     * Checks whether the sync already ran this calendar month.
     */
    private function hasRunThisMonth(): bool
    {
        if (!file_exists($this->logFile)) {
            return false;
        }

        $lastRun = trim(file_get_contents($this->logFile));
        $lastDate = \DateTime::createFromFormat('Y-m-d', $lastRun);
        $now = new \DateTime();

        return $lastDate && $lastDate->format('Y-m') === $now->format('Y-m');
    }

    /**
     * Updates the last run timestamp to the current date.
     */
    private function updateLastRunTimestamp(): void
    {
        file_put_contents($this->logFile, date('Y-m-d'));
    }
}
