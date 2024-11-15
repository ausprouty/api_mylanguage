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

    // Getter and Setter methods for other properties...
}
