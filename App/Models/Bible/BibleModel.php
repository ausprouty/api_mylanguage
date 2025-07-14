<?php

namespace App\Models\Bible;

use ReflectionClass;

/**
 * Represents a Bible resource, including metadata for language, media types,
 * and fileset classification. Used for integration with external sources like BibleBrain.
 */
class BibleModel
{

    // Bible metadata fields
    private $abbreviation;
    private $audio;
    private $bid;
    private $collectionCode;
    private $dateVerified;
    private $direction;
    private $externalId;
    private $format;
    private $idBibleGateway;
    private $languageCodeBibleBrain;
    private $languageCodeDrupal;
    private $languageCodeHL;
    private $languageCodeIso;
    private $languageEnglish;
    private $languageName;
    private $noBoldPdf;
    private $numerals;
    private $source;
    private $spacePdf;
    private $text;
    private $video;
    private $volumeName;
    private $volumeNameAlt;
    private $weight;

    /**
     * Populates the model with data using reflection.
     *
     * @param array $data Key-value array where keys match property names.
     */
    public function populate(array $data): void
    {
        $reflection = new ReflectionClass($this);
        foreach ($data as $key => $value) {
            if ($reflection->hasProperty($key)) {
                $property = $reflection->getProperty($key);
                $property->setAccessible(true);
                $property->setValue($this, $value);
            }
        }
    }

    /**
     * Resets all media type flags (text/audio/video) to false (0).
     */
    public function resetMediaFlags(): void
    {
        $this->text = 0;
        $this->audio = 0;
        $this->video = 0;
    }

    /**
     * Sets the text flag.
     */
    public function setText(bool $value): void
    {
        $this->text = $value ? 1 : 0;
    }

    /**
     * Sets the audio flag.
     */
    public function setAudio(bool $value): void
    {
        $this->audio = $value ? 1 : 0;
    }

    /**
     * Sets the video flag.
     */
    public function setVideo(bool $value): void
    {
        $this->video = $value ? 1 : 0;
    }

    /**
     * Returns all properties as an associative array.
     */
    public function getProperties(): array
    {
        $reflection = new ReflectionClass($this);
        $propsArray = [];
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $propsArray[$property->getName()] = $property->getValue($this);
        }
        return $propsArray;
    }

    // ----------------------
    // Getters for properties
    // ----------------------

    public function getAbbreviation(): ?string { return $this->abbreviation; }

    public function getAudio(): int { return $this->audio; }

    public function getBid(): int { return $this->bid; }

    public function getCollectionCode(): ?string { return $this->collectionCode; }

    public function getDateVerified(): ?string { return $this->dateVerified; }

    public function getDirection(): ?string { return $this->direction; }

    public function getExternalId(): ?string { return $this->externalId; }

    public function getFormat(): ?string { return $this->format; }

    public function getIdBibleGateway(): ?string { return $this->idBibleGateway; }

    public function getLanguageCodeBibleBrain(): ?int { return $this->languageCodeBibleBrain; }

    public function getLanguageCodeDrupal(): ?string { return $this->languageCodeDrupal; }

    public function getLanguageCodeHL(): ?string { return $this->languageCodeHL; }

    public function getLanguageCodeIso(): ?string { return $this->languageCodeIso; }

    public function getLanguageEnglish(): ?string { return $this->languageEnglish; }

    public function getLanguageName(): ?string { return $this->languageName; }

    public function getNoBoldPdf(): ?string { return $this->noBoldPdf; }

    public function getNumerals(): ?string { return $this->numerals; }

    public function getSource(): ?string { return $this->source; }

    public function getSpacePdf(): ?string { return $this->spacePdf; }

    public function getText(): int { return $this->text; }

    public function getVideo(): int { return $this->video; }

    public function getVolumeName(): ?string { return $this->volumeName; }

    public function getVolumeNameAlt(): ?string { return $this->volumeNameAlt; }

    public function getWeight(): ?int { return $this->weight; }
}
