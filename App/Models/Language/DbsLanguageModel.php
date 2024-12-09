<?php

namespace App\Models\Language;

class DbsLanguageModel {
    private $languageCodeHL;
    private $collectionCode;
    private $format;

    /**
     * Populates the model with data from an associative array.
     *
     * @param array $data Associative array with keys matching property names.
     */
    public function populate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
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
