<?php

namespace App\Cron;

use App\Repositories\BibleBrainLanguageRepository;
use App\Repositories\LanguageRepository;
use App\Services\Web\BibleBrainConnectionService;
use App\Services\LoggerService;

/**
 * Syncs all BibleBrain language metadata to the local hl_languages table.
 * Meant to be run once per month via cron.
 */
class BibleBrainLanguageSyncService
{
    private BibleBrainLanguageRepository $bibleBrainLanguageRepository;
    private LanguageRepository $languageRepository;
    private string $logFile;

    public function __construct(BibleBrainLanguageRepository $bibleBrainLanguageRepository,LanguageRepository $languageRepository)
    {
        $this->bibleBrainLanguageRepository = $bibleBrainLanguageRepository;
        $this->languageRepository = $languageRepository;
        $this->logFile = __DIR__ . '/../../data/cron/last_biblebrain_language_sync.txt';
    }

    /**
     * Runs BibleBrain sync if not already done this month.
     */
    public function syncOncePerMonth(): void
    {
        if ($this->hasRunThisMonth()) {
            loggerService::logInfo('bibleBrainLanguageSync', 'Sync already performed this month. Skipping.');
            return;
        }

        $this->syncAllBibleBrainLanguages();
        $this->updateLastRunTimestamp();
        loggerService::logInfo('bibleBrainLanguageSync', 'Sync completed and timestamp updated.');
    }

    /**
     * Main sync loop: pulls all languages from BibleBrain and updates local DB.
     */
    private function syncAllBibleBrainLanguages(): void
    {
        $page = 1;
        $limit = 100;

        do {
            $url = "languages?limit={$limit}&page={$page}&v=4";
            loggerService::logInfo('SyncAllBibleBrainLanguages-49', $url);
            $response = new BibleBrainConnectionService($url);
            $data = $response->response->data ?? [];
            
            if (empty($data)) {
                break;
            }

            foreach ($data as $entry) {
                $languageCodeIso = $entry->iso ?? null;
                $languageCodeBibleBrain = $entry->id ?? null;
                loggerService::logInfo('SyncAllBibleBrainLanguages-65', " $languageCodeBibleBrain --   $languageCodeIso");
                $name = $entry->name ?? null;
                $autonym = $entry->autonym ?? null;

                if (!$languageCodeIso || !$languageCodeBibleBrain) {
                    continue;
                }
                // see if language is already in our database; if so, skip 
                $bibleBrain = $this->bibleBrainLanguageRepository->bibleBrainLanguageRecordExists($languageCodeBibleBrain);
                if ($bibleBrain){
                    loggerService::logInfo('SyncAllBibleBrainLanguages-73 - existing', " $languageCodeBibleBrain --   $languageCodeIso");
                    continue;
                }
                // now we will update existing records with the same languageCodeIso that
                // do not already have a languageCodeBibieBrain
                $existing = $this->languageRepository->getLanguageCodesFromIso($languageCodeIso);
                loggerService::logInfo('SyncAllBibleBrainLanguages-74',$existing);
                if (empty($existing->languageCodeHL)) {
                     loggerService::logInfo('SyncAllBibleBrainLanguages-81 - new', " $languageCodeBibleBrain --   $languageCodeIso");
                    $this->languageRepository->insertLanguage($languageCodeIso, $name);
                }

                if (empty($existing->languageCodeBibleBrain)) {
                    loggerService::logInfo('SyncAllBibleBrainLanguages-80', $languageCodeBibleBrain);
                    $this->bibleBrainLanguageRepository->updateLanguageCodeBibleBrain($languageCodeIso, $languageCodeBibleBrain);
                }
                if ($autonym) {
                    $ethnics = $this->languageRepository->getEthnicNamesForLanguageIso($languageCodeIso);
                    if (!in_array($autonym, $ethnics, true)) {
                        loggerService::logInfo('SyncAllBibleBrainLanguages-87', $autonym);
                        $this->languageRepository->updateEthnicName($languageCodeIso, $autonym);
                    }
                }
            }

            $page++;
            sleep(1);

        } while (!empty($data));
    }

    /**
     * Checks whether sync has already run this calendar month.
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
     * Updates the file with the current date to mark last run.
     */
    private function updateLastRunTimestamp(): void
    {
        file_put_contents($this->logFile, date('Y-m-d'));
    }

    /**
     * Test method: Fetch and log 5 sample BibleBrain language entries.
     */
    public function testLogFiveBibleBrainLanguages(): void
    {
        $url = 'languages?limit=5&page=1&v=4';
        $response = new BibleBrainConnectionService($url);
        $data = $response->response->data ?? [];

        foreach ($data as $entry) {
            $logEntry = [
                'id' => $entry->id ?? 'N/A',
                'languageCodeIso' => $entry->iso ?? 'N/A',
                'name' => $entry->name ?? 'N/A',
                'autonym' => $entry->autonym ?? 'N/A',
                'bibles' => $entry->bibles ?? 0,
                'filesets' => $entry->filesets ?? 0,
            ];
            loggerService::logInfo('bibleBrainLanguageSync', $logEntry);
        }
    }
}
