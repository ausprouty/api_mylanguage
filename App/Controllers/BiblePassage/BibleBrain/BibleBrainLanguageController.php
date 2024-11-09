<?php
namespace App\Controllers\BiblePassage\BibleBrain;

use App\Models\Data\BibleBrainConnectionModel;
use App\Models\Language\LanguageModel;
use App\Repositories\LanguageRepository;

class BibleBrainLanguageController
{
    private $databaseService;
    private $languageRepository;
    public $response;
    public $languageCodeIso;
    public $LanguageCodeBibleBrain;
    public $iso;
    public $name;
    public $autonym;
    private $bibles;
    private $filesets;
    private $rolv_code;

    public function __construct(LanguageRepository $languageRepository)
    {
        $this->languageRepository = $languageRepository;
    }

    public function getLanguagesFromCountryCode($countryCode)
    {
        $url = 'https://4.dbt.io/api/languages?country=' . $countryCode;
        $languages = new BibleBrainConnectionModel($url);
        $this->response = $languages->response;
    }

    public function clearCheckedBBBibles()
    {
        $this->languageRepository->clearCheckedBBBibles();
    }

    public function getNextLanguageforLanguageDetails()
    {
        $this->languageCodeIso = $this->languageRepository->getNextLanguageforLanguageDetails();
        return $this->languageCodeIso;
    }

    public function setLanguageDetailsComplete($languageCodeIso)
    {
        $this->languageRepository->setLanguageDetailsComplete($languageCodeIso);
    }

    public function updateFromLanguageCodeIso($languageCodeIso, $name)
    {
        $data = $this->languageRepository->getLanguageCodes($languageCodeIso);

        if (!$data->languageCodeHL) {
            $this->languageRepository->insertLanguage($languageCodeIso, $name);
        }
        if (!$data->languageCodeBibleBrain) {
            if ($this->getLanguageDetails($languageCodeIso)) {
                $this->updateBibleBrainLanguageDetails();
            }
        }
    }

    public function getLanguageDetails($languageCodeIso)
    {
        $url = 'https://4.dbt.io/api/languages?language_code=' . $languageCodeIso;
        $languageDetails = new BibleBrainConnectionModel($url);

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
        if (!$this->LanguageCodeBibleBrain) {
            return;
        }

        if (!$this->languageRepository->BibleBrainLanguageRecordExists($this->LanguageCodeBibleBrain)) {
            if ($this->languageRepository->LanguageIsoRecordExists($this->iso)) {
                $ethnicNames = $this->languageRepository->getEthnicNamesForLanguageIso($this->iso);
                $found = in_array($this->autonym, $ethnicNames);

                if (!$found) {
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
