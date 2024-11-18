<?php

namespace App\Models\Bible;

use App\Repositories\BibleRepository;
use ReflectionClass;
use Exception;

/**
 * Represents a Bible entity.
 */
class BibleModel
{
    private $repository;

    private $abbreviation;
    private $audio;
    private $bid;
    private $collectionCode;
    private $dateVerified;
    private $direction;
    private $externalId;
    private $format;
    private $idBibleGateway;
    private $languageCode;
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

    public function __construct(BibleRepository $repository)
    {
        $this->repository = $repository;
        $this->initializeDefaultValues();
    }

    /**
     * Initializes default values for all properties.
     */
    private function initializeDefaultValues()
    {
        $this->bid = 0;
        $this->source = '';
        $this->externalId = '';
        $this->volumeName = '';
        $this->volumeNameAlt = '';
        $this->languageName = '';
        $this->languageEnglish = '';
        $this->languageCodeHL = '';
        $this->languageCodeIso = '';
        $this->idBibleGateway = '';
        $this->collectionCode = '';
        $this->direction = '';
        $this->numerals = '';
        $this->spacePdf = '';
        $this->noBoldPdf = '';
        $this->format = '';
        $this->text = 0;
        $this->audio = 0;
        $this->video = 0;
        $this->weight = 0;
        $this->dateVerified = '';
    }

    /**
     * Populates the model with data from an associative array.
     *
     * @param array $data Associative array of property values.
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
     * Resets the media type flags (text, audio, video) to zero.
     */
    public function resetMediaFlags(): void
    {
        $this->text = 0;
        $this->audio = 0;
        $this->video = 0;
    }

    /**
     * Sets the text media flag.
     *
     * @param bool $value The value to set (true/false).
     */
    public function setText(bool $value): void
    {
        $this->text = $value ? 1 : 0;
    }

    /**
     * Sets the audio media flag.
     *
     * @param bool $value The value to set (true/false).
     */
    public function setAudio(bool $value): void
    {
        $this->audio = $value ? 1 : 0;
    }

    /**
     * Sets the video media flag.
     *
     * @param bool $value The value to set (true/false).
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

    /**
     * Get the abbreviation.
     *
     * @return string|null
     */
    public function getAbbreviation(): ?string
    {
        return $this->abbreviation;
    }

    /**
     * Get the audio flag.
     *
     * @return int
     */
    public function getAudio(): int
    {
        return $this->audio;
    }

    /**
     * Get the Bible ID (bid).
     *
     * @return int
     */
    public function getBid(): int
    {
        return $this->bid;
    }

    /**
     * Get the collection code.
     *
     * @return string
     */
    public function getCollectionCode(): string
    {
        return $this->collectionCode;
    }

    /**
     * Get the date verified.
     *
     * @return string
     */
    public function getDateVerified(): string
    {
        return $this->dateVerified;
    }

    /**
     * Get the direction.
     *
     * @return string
     */
    public function getDirection(): string
    {
        return $this->direction;
    }

    /**
     * Get the external ID.
     *
     * @return string
     */
    public function getExternalId(): string
    {
        return $this->externalId;
    }

    /**
     * Get the format.
     *
     * @return string
     */
    public function getFormat(): string
    {
        return $this->format;
    }

    /**
     * Get the Bible Gateway ID.
     *
     * @return string
     */
    public function getIdBibleGateway(): string
    {
        return $this->idBibleGateway;
    }

    /**
     * Get the language code.
     *
     * @return string
     */
    public function getLanguageCode(): string
    {
        return $this->languageCode;
    }

    /**
     * Get the language code (Drupal).
     *
     * @return string
     */
    public function getLanguageCodeDrupal(): string
    {
        return $this->languageCodeDrupal;
    }

    /**
     * Get the language code (HL).
     *
     * @return string
     */
    public function getLanguageCodeHL(): string
    {
        return $this->languageCodeHL;
    }

    /**
     * Get the language code (ISO).
     *
     * @return string
     */
    public function getLanguageCodeIso(): string
    {
        return $this->languageCodeIso;
    }

    /**
     * Get the language English name.
     *
     * @return string
     */
    public function getLanguageEnglish(): string
    {
        return $this->languageEnglish;
    }

    /**
     * Get the language name.
     *
     * @return string
     */
    public function getLanguageName(): string
    {
        return $this->languageName;
    }

    /**
     * Get the no-bold PDF setting.
     *
     * @return string
     */
    public function getNoBoldPdf(): string
    {
        return $this->noBoldPdf;
    }

    /**
     * Get the numerals.
     *
     * @return string
     */
    public function getNumerals(): string
    {
        return $this->numerals;
    }

    /**
     * Get the source.
     *
     * @return string
     */
    public function getSource(): string
    {
        return $this->source;
    }

    /**
     * Get the space PDF setting.
     *
     * @return string
     */
    public function getSpacePdf(): string
    {
        return $this->spacePdf;
    }

    /**
     * Get the text flag.
     *
     * @return int
     */
    public function getText(): int
    {
        return $this->text;
    }

    /**
     * Get the video flag.
     *
     * @return int
     */
    public function getVideo(): int
    {
        return $this->video;
    }

    /**
     * Get the volume name.
     *
     * @return string
     */
    public function getVolumeName(): string
    {
        return $this->volumeName;
    }

    /**
     * Get the alternate volume name.
     *
     * @return string
     */
    public function getVolumeNameAlt(): string
    {
        return $this->volumeNameAlt;
    }

    /**
     * Get the weight.
     *
     * @return int
     */
    public function getWeight(): int
    {
        return $this->weight;
    }
}
