<?php

namespace App\Models\Language;

use ReflectionClass;

/**
 * Represents a Language entity with associated properties and methods.
 */
class LanguageModel
{
    private $id;
    private $name;
    private $ethnicName;
    private $languageCodeBibleBrain;
    private $languageCodeBing;
    private $languageCodeBrowser;
    private $languageCodeDrupal;
    private $languageCodeGoogle;
    private $languageCodeHL;
    private $languageCodeIso;
    private $languageCodeJF;
    private $languageCodeTracts;
    private $direction;
    private $numeralSet;
    private $isChinese;
    private $isHindu;
    private $font;
    private $fontData;
    private $mylanguage;
    private $requests;
    private $checkedBBBibles;

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

    /**
     * Returns the language properties as an associative array.
     *
     * @return array
     */
    public function getProperties(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties();
        $propsArray = [];

        foreach ($properties as $property) {
            $property->setAccessible(true); // Allows access to private property
            $propsArray[$property->getName()] = $property->getValue($this);
        }

        return $propsArray;
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getName(): ?string { return $this->name; }
    public function getEthnicName(): ?string { return $this->ethnicName; }
    public function getLanguageCodeBibleBrain(): ?int { return $this->languageCodeBibleBrain; }
    public function getLanguageCodeBing(): ?string { return $this->languageCodeBing; }
    public function getLanguageCodeBrowser(): ?string { return $this->languageCodeBrowser; }
    public function getLanguageCodeDrupal(): ?string { return $this->languageCodeDrupal; }
    public function getLanguageCodeGoogle(): ?string { return $this->languageCodeGoogle; }
    public function getLanguageCodeHL(): ?string { return $this->languageCodeHL; }
    public function getLanguageCodeIso(): ?string { return $this->languageCodeIso; }
    public function getLanguageCodeJF(): ?int { return $this->languageCodeJF; }
    public function getLanguageCodeTracts(): ?string { return $this->languageCodeTracts; }
    public function getDirection(): ?string { return $this->direction; }
    public function getNumeralSet(): ?string { return $this->numeralSet; }
    public function getIsChinese(): ?bool { return $this->isChinese; }
    public function getIsHindu(): ?bool { return $this->isHindu; }
    public function getFont(): ?string { return $this->font; }
    public function getFontData(): ?string { return $this->fontData; }
    public function getMyLanguage(): ?string { return $this->mylanguage; }
    public function getRequests(): ?int { return $this->requests; }
    public function getCheckedBBBibles(): ?string { return $this->checkedBBBibles; }
}
