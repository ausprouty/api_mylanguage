<?php

namespace App\Models\Language;

class DbsLanguageModel {
    private $languageCodeHL;
    private $collectionCode;
    private $format;

    public function __construct($languageCodeHL = null, $collectionCode = null, $format = null)
    {
        $this->languageCodeHL = $languageCodeHL;
        $this->collectionCode = $collectionCode;
        $this->format = $format;
    }

    // Getters
    public function getLanguageCodeHL()
    {
        return $this->languageCodeHL;
    }

    public function getCollectionCode()
    {
        return $this->collectionCode;
    }

    public function getFormat()
    {
        return $this->format;
    }

    // Setters
    public function setLanguageCodeHL($languageCodeHL)
    {
        $this->languageCodeHL = $languageCodeHL;
    }

    public function setCollectionCode($collectionCode)
    {
        $this->collectionCode = $collectionCode;
    }

    public function setFormat($format)
    {
        $this->format = $format;
    }
}
