<?php

namespace App\Models\Language;

use App\Repositories\LanguageRepository;

class LanguageModel {
    private $id;
    private $name;
    private $ethnicName;
    private $languageCodeBibleBrain;
    private $languageCodeHL;
    private $languageCodeIso;
    private $languageCodeBing;
    private $languageCodeBrowser;
    private $languageCodeGoogle;
    private $direction;
    private $numeralSet;
    private $isChinese;
    private $font;
    private $fontData;

    private $repository;

    public function __construct(LanguageRepository $repository) {
        $this->repository = $repository;
    }

    /**
     * Loads a language by its HL code and populates the model's properties
     */
    public function loadByCodeHL($languageCodeHL) {
        $data = $this->repository->findOneByLanguageCodeHL($languageCodeHL);
        if ($data) {
            $this->populate($data);
        }
    }

    /**
     * Populates model properties with data
     */
    private function populate($data) {
        $this->id = $data->id;
        $this->name = $data->name;
        $this->ethnicName = $data->ethnicName;
        $this->languageCodeBibleBrain = $data->languageCodeBibleBrain;
        $this->languageCodeHL = $data->languageCodeHL;
        $this->languageCodeIso = $data->languageCodeIso;
        $this->languageCodeBing = $data->languageCodeBing;
        $this->languageCodeBrowser = $data->languageCodeBrowser;
        $this->languageCodeGoogle = $data->languageCodeGoogle;
        $this->direction = $data->direction;
        $this->numeralSet = $data->numeralSet;
        $this->isChinese = $data->isChinese;
        $this->font = $data->font;
        $this->fontData = $data->fontData ? json_decode($data->fontData, true) : null;
    }

    // Getters for accessing properties of the language model
    public function getId() {
        return $this->id;
    }

    public function getName() {
        return $this->name;
    }

    public function getEthnicName() {
        return $this->ethnicName;
    }

    public function getLanguageCodeBibleBrain() {
        return $this->languageCodeBibleBrain;
    }

    public function getLanguageCodeHL() {
        return $this->languageCodeHL;
    }

    public function getLanguageCodeIso() {
        return $this->languageCodeIso;
    }

    public function getLanguageCodeBing() {
        return $this->languageCodeBing;
    }

    public function getLanguageCodeBrowser() {
        return $this->languageCodeBrowser;
    }

    public function getLanguageCodeGoogle() {
        return $this->languageCodeGoogle;
    }

    public function getDirection() {
        // Ensure default to 'ltr' if not 'rtl'
        return $this->direction === 'rtl' ? 'rtl' : 'ltr';
    }

    public function getNumeralSet() {
        return $this->numeralSet;
    }

    public function getIsChinese() {
        return $this->isChinese;
    }

    public function getFont() {
        return $this->font;
    }

    public function getFontData() {
        return $this->fontData;
    }

    /**
     * Creates a new language entry from a BibleBrain API record
     */
    public function createFromBibleBrainRecord($record) {
        $languageCodeHL = $record->iso . '24';
        $this->name = $record->name ?? 'Unknown';
        $this->ethnicName = $record->autonym;
        $this->languageCodeIso = $record->iso;
        $this->languageCodeBibleBrain = $record->id;
        $this->languageCodeHL = $languageCodeHL;

        $this->repository->createLanguage($this); // Save to database through repository
    }

    /**
     * Updates the ethnic name for a given ISO code.
     * This modifies both the model's property and updates the database.
     */
    public function updateEthnicName($ethnicName) {
        $this->ethnicName = $ethnicName;
        $this->repository->updateEthnicNameFromIso($this->languageCodeIso, $ethnicName);
    }

    /**
     * Updates the BibleBrain language code for a given ISO code.
     * This modifies both the model's property and updates the database.
     */
    public function updateBibleBrainCode($languageCodeBibleBrain) {
        $this->languageCodeBibleBrain = $languageCodeBibleBrain;
        $this->repository->updateLanguageCodeBibleBrainFromIso($this->languageCodeIso, $languageCodeBibleBrain);
    }
}
