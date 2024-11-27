<?php

namespace App\Models\Bible;

use ReflectionClass;

/**
 * Represents Bible reference information.
 */
class PassageReferenceModel
{
    private $entry;
    private $languageCodeHL;
    private $languageCodeIso;
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

    public function __construct()
    {
        $this->entry = ' ';
        $this->languageCodeHL = null;
        $this->languageCodeIso = null;
        $this->bookName = ' ';
        $this->bookID = null;
        $this->uversionBookID = null;
        $this->bookNumber = 1;
        $this->testament = null;
        $this->chapterStart = null;
        $this->verseStart = null;
        $this->chapterEnd = null;
        $this->verseEnd = null;
        $this->passageID = null;
    }

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
    public function getBookID()
    {
        return $this->bookID;
    }
    public function getBookName()
    {
        return $this->bookName;
    }
    public function getBookNumber()
    {
        return $this->bookNumber;
    }
    public function getChapterStart()
    {
        return $this->chapterStart;
    }
    public function getEntry()
    {
        return $this->entry;
    }
    public function getLanguageCodeHL()
    {
        return $this->languageCodeHL;
    }
    public function getLanguageCodeIso()
    {
        return $this->languageCodeIso;
    }
    public function getPassageID()
    {
        return $this->passageID;
    }
    public function getTestament()
    {
        return $this->testament;
    }
    public function getUversionBookID()
    {
        return $this->uversionBookID;
    }
    public function getVerseEnd()
    {
        return $this->verseEnd;
    }
    public function getVerseStart()
    {
        return $this->verseStart;
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
