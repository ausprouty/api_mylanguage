<?php

namespace App\Services\Bible;

use App\Services\Web\BibleBrainConnectionService;
use App\Models\Language\LanguageModel;
use App\Repositories\LanguageRepository;

class BibleBrainLanguageService
{
    private LanguageRepository $languageRepository;
    private ?string $languageCodeBibleBrain = null;
    private ?string $iso = null;
    private ?string $name = null;
    private ?string $autonym = null;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function fetchLanguagesByCountry(string $countryCode): ?object
    {
        $url = 'https://4.dbt.io/api/languages?country=' . urlencode($countryCode);
        $languages = new BibleBrainConnectionService($url);
        return $languages->response;
    }

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

    public function fetchLanguageDetails(string $languageCodeIso): ?LanguageModel
    {
        $url = 'https://4.dbt.io/api/languages?language_code=' . urlencode($languageCodeIso);
        $languageDetails = new BibleBrainConnectionService($url);
    
        if (!empty($languageDetails->response->data[0])) {
            $data = $languageDetails->response->data[0];
            $language = new LanguageModel($this->languageRepository); // Instantiate the model
    
            // Populate the model with data
            $language->createFromBibleBrainRecord(
                $data->id ?? null,
                $data->iso ?? null,
                $data->name ?? null,
                $data->autonym ?? null
            );
    
            return $language; // Return the populated model
        }
    
        return null; // Return null if no data found
    }
    

    private function updateBibleBrainLanguageDetails(): void
    {
        if (!$this->languageCodeBibleBrain) {
            return;
        }

        if (!$this->languageRepository->bibleBrainLanguageRecordExists($this->languageCodeBibleBrain)) {
            if ($this->languageRepository->languageIsoRecordExists($this->iso)) {
                $ethnicNames = $this->languageRepository->getEthnicNamesForLanguageIso($this->iso);

                if (!in_array($this->autonym, $ethnicNames, true)) {
                    $this->languageRepository->updateEthnicName($this->iso, $this->autonym);
                }
                $this->languageRepository->updateLanguageCodeBibleBrain($this->iso, $this->languageCodeBibleBrain);
            } else {
                $language = new LanguageModel($this->languageRepository);
                $language->createFromBibleBrainRecord(
                    $this->languageCodeBibleBrain,
                    $this->iso,
                    $this->name,
                    $this->autonym
                );
                $this->languageRepository->createLanguageFromBibleBrainRecord($language);
            }
        }
    }
}
