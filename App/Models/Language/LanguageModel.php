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

    /**
     * Constructor to initialize properties with default values.
     */
    public function __construct()
    {
        $this->id = null;
        $this->name = null;
        $this->ethnicName = null;
        $this->languageCodeBibleBrain = null;
        $this->languageCodeHL = null;
        $this->languageCodeIso = null;
        $this->languageCodeBing = null;
        $this->languageCodeBrowser = null;
        $this->languageCodeGoogle = null;
        $this->direction = 'ltr';
        $this->numeralSet = null;
        $this->isChinese = null;
        $this->font = null;
        $this->fontData = null;
    }

    /**
     * Populates the model with data from an associative array.
     *
     * @param array $data The data to populate the model with.
     */
    public function populate(array $data): void
    {
        $this->id = $data['id'] ?? null;
        $this->name = $data['name'] ?? null;
        $this->ethnicName = $data['ethnicName'] ?? null;
        $this->languageCodeBibleBrain = $data['languageCodeBibleBrain'] ?? null;
        $this->languageCodeHL = $data['languageCodeHL'] ?? null;
        $this->languageCodeIso = $data['languageCodeIso'] ?? null;
        $this->languageCodeBing = $data['languageCodeBing'] ?? null;
        $this->languageCodeBrowser = $data['languageCodeBrowser'] ?? null;
        $this->languageCodeGoogle = $data['languageCodeGoogle'] ?? null;
        $this->direction = $data['direction'] ?? 'ltr';
        $this->numeralSet = $data['numeralSet'] ?? null;
        $this->isChinese = $data['isChinese'] ?? null;
        $this->font = $data['font'] ?? null;
        $this->fontData = isset($data['fontData']) ? json_decode($data['fontData'], true) : null;
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
    public function getDirection(): string { return $this->direction; }
    public function getEthnicName(): ?string { return $this->ethnicName; }
    public function getFont(): ?string { return $this->font; }
    public function getFontData(): ?array { return $this->fontData; }
    public function getId(): ?int { return $this->id; }
    public function getIsChinese(): ?bool { return $this->isChinese; }
    public function getLanguageCodeBibleBrain(): ?string { return $this->languageCodeBibleBrain; }
    public function getLanguageCodeBing(): ?string { return $this->languageCodeBing; }
    public function getLanguageCodeBrowser(): ?string { return $this->languageCodeBrowser; }
    public function getLanguageCodeGoogle(): ?string { return $this->languageCodeGoogle; }
    public function getLanguageCodeHL(): ?string { return $this->languageCodeHL; }
    public function getLanguageCodeIso(): ?string { return $this->languageCodeIso; }
    public function getName(): ?string { return $this->name; }
    public function getNumeralSet(): ?string { return $this->numeralSet; }
}
