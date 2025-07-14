<?php

namespace App\Services\Bible;

use App\Services\Web\BibleBrainConnectionService;
use App\Models\Language\LanguageModel;
use App\Repositories\BibleBrainLanguageRepository;
use App\Factories\LanguageFactory;

/**
 * Service class to handle synchronization of language metadata from BibleBrain.
 * Ensures local language records are complete with BibleBrain codes and autonyms.
 */
class BibleBrainLanguageService
{
    private BibleBrainLanguageRepository $languageRepository;
    private LanguageFactory $languageFactory;

    // Temporary fields used when updating BibleBrain metadata
    private ?string $languageCodeBibleBrain = null;
    private ?string $iso = null;
    private ?string $name = null;
    private ?string $autonym = null;

    /**
     * Constructor.
     *
     * @param BibleBrainLanguageRepository $languageRepository
     * @param LanguageFactory $languageFactory
     */
    public function __construct(
        BibleBrainLanguageRepository $languageRepository,
        LanguageFactory $languageFactory
    ) {
        $this->languageRepository = $languageRepository;
        $this->languageFactory = $languageFactory;
    }

    /**
 * Test method: Fetches 5 BibleBrain languages and logs their basic data.
 * Used for verifying connection and data shape.
 */
public function testLogFiveBibleBrainLanguages(): void
{
    $url = '/languages?limit=5&page=1&v=4';
    $response = new BibleBrainConnectionService($url);
    $data = $response->response->data ?? [];

    foreach ($data as $entry) {
        $logEntry = [
            'id' => $entry->id ?? 'N/A',
            'iso' => $entry->iso ?? 'N/A',
            'name' => $entry->name ?? 'N/A',
            'autonym' => $entry->autonym ?? 'N/A',
            'bibles' => $entry->bibles ?? 0,
            'filesets' => $entry->filesets ?? 0,
        ];
        loggerService::logInfo('bibleBrain', $logEntry);
    }
}

   
    /**
     * Synchronizes all languages from BibleBrain into the local database.
     * Loops through all pages from the BibleBrain API and processes each language.
     */
    public function syncAllBibleBrainLanguages(): void
    {
        $page = 1;
        $limit = 100;

        do {
            $url = "/languages?limit={$limit}&page={$page}&v=4";
            $response = new BibleBrainConnectionService($url);
            $data = $response->response->data ?? [];

            if (empty($data)) {
                break;
            }

            foreach ($data as $entry) {
                $iso = $entry->iso ?? null;
                $id = $entry->id ?? null;
                $name = $entry->name ?? null;
                $autonym = $entry->autonym ?? null;

                if (!$iso || !$id) {
                    continue;
                }

                $language = $this->languageRepository->getLanguageCodes($iso);

                if (empty($language->languageCodeHL)) {
                    $this->languageRepository->insertLanguage($iso, $name);
                }

                if (empty($language->languageCodeBibleBrain)) {
                    $this->languageRepository->updateLanguageCodeBibleBrain($iso, $id);
                }

                if ($autonym) {
                    $ethnics = $this->languageRepository->getEthnicNamesForLanguageIso($iso);
                    if (!in_array($autonym, $ethnics, true)) {
                        $this->languageRepository->updateEthnicName($iso, $autonym);
                    }
                }
            }

            $page++;
            sleep(1); // avoid hammering the API

        } while (!empty($data));
    }
    /**
     * Fetches a list of languages spoken in a given country using BibleBrain's API.
     *
     * @param string $countryCode ISO 3166-1 alpha-2 country code (e.g., 'ID', 'PH')
     * @return object|null BibleBrain API response
     */
    public function fetchLanguagesByCountry(string $countryCode): ?object
    {
        $url = '/languages?country=' . urlencode($countryCode);
        $languages = new BibleBrainConnectionService($url);

        return $languages->response;
    }

    /**
     * Ensures a local record exists for the given ISO code and updates it with BibleBrain metadata if needed.
     *
     * @param string $languageCodeIso ISO code (e.g., 'ace')
     * @param string $name Display name for fallback insertion
     */
    public function processLanguageUpdate(string $languageCodeIso, string $name): void
    {
        $data = $this->languageRepository->getLanguageCodes($languageCodeIso);

        if (empty($data->languageCodeHL)) {
            $this->languageRepository->insertLanguage($languageCodeIso, $name);
        }

        if (empty($data->languageCodeBibleBrain) && $this->fetchLanguageDetails($languageCodeIso)) {
            $this->updateBibleBrainLanguageDetails();
        }
    }

    /**
     * Fetches detailed metadata for a language by ISO code from BibleBrain.
     *
     * @param string $languageCodeIso ISO code to fetch from BibleBrain
     * @return LanguageModel|null Returns LanguageModel on success, null on failure
     */
    public function fetchLanguageDetails(string $languageCodeIso): ?LanguageModel
    {
        $url = '/languages?language_code=' . urlencode($languageCodeIso);
        $languageDetails = new BibleBrainConnectionService($url);

        if (!empty($languageDetails->response->data[0])) {
            $data = $languageDetails->response->data[0];

            $this->languageCodeBibleBrain = $data->id ?? null;
            $this->iso = $data->iso ?? null;
            $this->name = $data->name ?? null;
            $this->autonym = $data->autonym ?? null;

            $language = $this->languageFactory->create([
                'languageCodeBibleBrain' => $this->languageCodeBibleBrain,
                'languageCodeIso' => $this->iso,
                'name' => $this->name,
                'ethnicName' => $this->autonym
            ]);

            $this->languageRepository->createLanguageFromBibleBrainRecord($language);

            return $language;
        }

        return null;
    }

    /**
     * Applies stored BibleBrain metadata to update or insert a local record.
     * Must be called after fetchLanguageDetails().
     */
    private function updateBibleBrainLanguageDetails(): void
    {
        if (empty($this->languageCodeBibleBrain)) {
            return;
        }

        if (!$this->languageRepository->bibleBrainLanguageRecordExists($this->languageCodeBibleBrain)) {
            if ($this->languageRepository->languageIsoRecordExists($this->iso)) {
                $ethnicNames = $this->languageRepository->getEthnicNamesForLanguageIso($this->iso);
                if (!in_array($this->autonym, $ethnicNames, true)) {
                    $this->languageRepository->updateEthnicName($this->iso, $this->autonym);
                }

                $this->languageRepository->updateLanguageCodeBibleBrain(
                    $this->iso,
                    $this->languageCodeBibleBrain
                );
            } else {
                $language = $this->languageFactory->create([
                    'languageCodeBibleBrain' => $this->languageCodeBibleBrain,
                    'languageCodeIso' => $this->iso,
                    'name' => $this->name,
                    'ethnicName' => $this->autonym
                ]);

                $this->languageRepository->createLanguageFromBibleBrainRecord($language);
            }
        }
    }
}
