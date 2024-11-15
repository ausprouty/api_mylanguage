<?php

namespace App\Services\Bible;

use App\Services\Web\BibleBrainConnectionService;
use App\Models\Language\LanguageModel;
use App\Repositories\LanguageRepository;
use App\Factories\LanguageModelFactory;

class BibleBrainLanguageService
{
    private LanguageRepository $languageRepository;
    private LanguageModelFactory $languageModelFactory;
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

        // Create and populate the LanguageModel using the factory
        $language = $this->languageModelFactory->create([
            'languageCodeBibleBrain' => $data->id ?? null,
            'languageCodeIso' => $data->iso ?? null,
            'name' => $data->name ?? null,
            'ethnicName' => $data->autonym ?? null
        ]);

        // Save the populated LanguageModel using the repository
        $this->languageRepository->createLanguageFromBibleBrainRecord($language);

        return $language; // Return the populated model
    }

    return null; // Return null if no data found
}

    

private function updateBibleBrainLanguageDetails(): void
{
    // Return early if no BibleBrain code is provided
    if (empty($this->languageCodeBibleBrain)) {
        return;
    }

    // Check if the BibleBrain language record exists
    if (!$this->languageRepository->bibleBrainLanguageRecordExists($this->languageCodeBibleBrain)) {
        // Check if a language record with this ISO code already exists
        if ($this->languageRepository->languageIsoRecordExists($this->iso)) {
            // Update ethnic name if the autonym is not already present
            $ethnicNames = $this->languageRepository->getEthnicNamesForLanguageIso($this->iso);
            if (!in_array($this->autonym, $ethnicNames, true)) {
                $this->languageRepository->updateEthnicName($this->iso, $this->autonym);
            }

            // Update the BibleBrain language code for the existing ISO record
            $this->languageRepository->updateLanguageCodeBibleBrain(
                $this->iso,
                $this->languageCodeBibleBrain
            );
        } else {
            // Create a new LanguageModel and save it
            $language = $this->languageModelFactory->create([
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
