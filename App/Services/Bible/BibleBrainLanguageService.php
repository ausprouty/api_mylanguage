<?php

namespace App\Services\Bible;

use App\Services\Web\BibleBrainConnectionService;
use App\Models\Language\LanguageModel;
use App\Repositories\LanguageRepository;

class BibleBrainLanguageService
{
    private $languageRepository;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function fetchLanguagesByCountry($countryCode)
    {
        $url = 'https://4.dbt.io/api/languages?country=' . $countryCode;
        $languages = new BibleBrainConnectionService($url);
        return $languages->response;
    }

    public function processLanguageUpdate($languageCodeIso, $name)
    {
        $data = $this->languageRepository->getLanguageCodes($languageCodeIso);

        if (!$data->languageCodeHL) {
            $this->languageRepository->insertLanguage($languageCodeIso, $name);
        }

        if (!$data->languageCodeBibleBrain && $this->fetchLanguageDetails($languageCodeIso)) {
            $this->updateBibleBrainLanguageDetails();
        }
    }

    private function fetchLanguageDetails($languageCodeIso)
    {
        $url = 'https://4.dbt.io/api/languages?language_code=' . $languageCodeIso;
        $languageDetails = new BibleBrainConnectionService($url);

        if (isset($languageDetails->response->data[0])) {
            $data = $languageDetails->response->data[0];
            $this->LanguageCodeBibleBrain = $data->id;
            $this->iso = $data->iso;
            $this->name = $data->name;
            $this->autonym = $data->autonym;
            return true;
        }

        return false;
    }

    public function updateBibleBrainLanguageDetails()
    {
        if (!$this->LanguageCodeBibleBrain) return;

        if (!$this->languageRepository->bibleBrainLanguageRecordExists($this->LanguageCodeBibleBrain)) {
            if ($this->languageRepository->languageIsoRecordExists($this->iso)) {
                $ethnicNames = $this->languageRepository->getEthnicNamesForLanguageIso($this->iso);

                if (!in_array($this->autonym, $ethnicNames)) {
                    $this->languageRepository->updateEthnicName($this->iso, $this->autonym);
                }
                $this->languageRepository->updateLanguageCodeBibleBrain($this->iso, $this->LanguageCodeBibleBrain);
            } else {
                $language = new LanguageModel();
                $language->populateFromBibleBrain($this->LanguageCodeBibleBrain, $this->iso, $this->name, $this->autonym);
                $this->languageRepository->createLanguageFromBibleBrainRecord($language);
            }
        }
    }
}
