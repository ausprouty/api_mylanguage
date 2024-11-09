<?php

namespace App\Models\Language;

use stdClass;

class CountryLanguageModel
{
    private $id;
    private $countryCode;
    private $languageCodeIso;
    private $languageCodeHL;
    private $languageNameEnglish;

    public function __construct($countryCode = '', $languageCodeHL = '', $languageNameEnglish = '')
    {
        $this->countryCode = $countryCode;
        $this->languageCodeHL = $languageCodeHL;
        $this->languageNameEnglish = $languageNameEnglish;
    }

    // Getters
    public function getCountryCode()
    {
        return $this->countryCode;
    }

    public function getLanguageCodeIso()
    {
        return $this->languageCodeIso;
    }

    public function getLanguageCodeHL()
    {
        return $this->languageCodeHL;
    }

    public function getLanguageNameEnglish()
    {
        return $this->languageNameEnglish;
    }

    // Setters
    public function setCountryCode($countryCode)
    {
        $this->countryCode = $countryCode;
    }

    public function setLanguageCodeIso($languageCodeIso)
    {
        $this->languageCodeIso = $languageCodeIso;
    }

    public function setLanguageCodeHL($languageCodeHL)
    {
        $this->languageCodeHL = $languageCodeHL;
    }

    public function setLanguageNameEnglish($languageNameEnglish)
    {
        $this->languageNameEnglish = $languageNameEnglish;
    }

    // Process languages to add custom language code JF
    public function addLanguageCodeJF(array $languages)
    {
        $data = [];
        foreach ($languages as $language) {
            $language->languageCodeJF = VideoModel::getLanguageCodeJF($language->languageCodeHL);
            $data[] = $language;
        }
        return $data;
    }
}
