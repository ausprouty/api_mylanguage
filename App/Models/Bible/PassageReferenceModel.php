<?php

namespace App\Models\Bible;

use ReflectionClass;
use App\Interfaces\ArclightVideoInterface; // Or wherever your interface is

/**
 * Represents Bible reference information.
 */
class PassageReferenceModel implements ArclightVideoInterface
{
    private $entry;
    private $bookName;
    private $bookID;
    private $uversionBookID;
    private $bookNumber;
    private $testament;
    private $chapterStart;
    private $verseStart;
    private $chapterEnd;
    private $verseEnd;
    private $passageID;

    private $videoSource;
    private $videoPrefix;
    private $videoCode;
    private $videoSegment;
    private $startTime;
    private $endTime;

    /**
     * Populates the model with data from an associative array.
     *
     * @param array $data Data to populate the model.
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
    public function getEntry(): ?string
    {
        return $this->entry;
    }

    public function getBookName(): ?string
    {
        return $this->bookName;
    }

    public function getBookID(): ?string
    {
        return $this->bookID;
    }

    public function getUversionBookID(): ?string
    {
        return $this->uversionBookID;
    }

    public function getBookNumber(): ?int   
    {
        return $this->bookNumber;
    }

    public function getTestament(): ?string
    {
        return $this->testament;
    }

    public function getChapterStart(): ?int   
    {
        return $this->chapterStart;
    }

    public function getVerseStart(): ?int  
    {
        return $this->verseStart;
    }

    public function getChapterEnd(): ?int
    {
        return $this->chapterEnd;
    }

    public function getVerseEnd(): ?int
    {
        return $this->verseEnd;
    }

    public function getPassageID(): ?string
    {
        return $this->passageID;
    }

    public function getVideoSource():? string
    {
        return $this->videoSource;
    }

    public function getVideoPrefix(): ?string  
    {
        return $this->videoPrefix;
    }

    public function getVideoCode() : ?string
    {
        return $this->videoCode;
    }

    public function getVideoSegment(): ?int
    {
        return $this->videoSegment;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }
    

    public function getEndTime(): ?string
    {
        return $this->endTime;
    }

    /**
     * Returns the properties as an associative array.
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
}
