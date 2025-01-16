<?php

namespace App\Models\Bible;

use ReflectionClass;

/**
 * Represents Bible reference information.
 */
class PassageReferenceModel
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
    public function getEntry()
    {
        return $this->entry;
    }

    public function getBookName()
    {
        return $this->bookName;
    }

    public function getBookID()
    {
        return $this->bookID;
    }

    public function getUversionBookID()
    {
        return $this->uversionBookID;
    }

    public function getBookNumber()
    {
        return $this->bookNumber;
    }

    public function getTestament()
    {
        return $this->testament;
    }

    public function getChapterStart()
    {
        return $this->chapterStart;
    }

    public function getVerseStart()
    {
        return $this->verseStart;
    }

    public function getChapterEnd()
    {
        return $this->chapterEnd;
    }

    public function getVerseEnd()
    {
        return $this->verseEnd;
    }

    public function getPassageID()
    {
        return $this->passageID;
    }

    public function getVideoSource()
    {
        return $this->videoSource;
    }

    public function getVideoPrefix()
    {
        return $this->videoPrefix;
    }

    public function getVideoCode()
    {
        return $this->videoCode;
    }

    public function getVideoSegment()
    {
        return $this->videoSegment;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getEndTime()
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
