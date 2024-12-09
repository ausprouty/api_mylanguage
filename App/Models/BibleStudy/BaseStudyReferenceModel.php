<?php

namespace App\Models\BibleStudy;

use ReflectionClass;

abstract class BaseStudyReferenceModel
{
    protected int $lesson;
    protected string $description;
    protected string $description_twig_key;
    protected string $reference;
    protected string $testament;
    protected string $passage_reference_info;
    protected ?string $bookName = null;
    protected ?string $bookID = null;
    protected ?string $chapterStart = null;
    protected ?string $chapterEnd = null;
    protected ?string $verseStart = null;
    protected ?string $verseEnd = null;
    protected ?string $passageID = null;
    protected ?string $uversionBookID = null;

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


    // Explicit Getters and Setters

    public function getLesson(): int
    {
        return $this->lesson;
    }

    public function setLesson(int $lesson): void
    {
        $this->lesson = $lesson;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function getDescriptionTwigKey(): string
    {
        return $this->description_twig_key;
    }

    public function setDescriptionTwigKey(string $description_twig_key): void
    {
        $this->description_twig_key = $description_twig_key;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    public function getTestament(): string
    {
        return $this->testament;
    }

    public function setTestament(string $testament): void
    {
        $this->testament = $testament;
    }

    public function getPassageReferenceInfo(): string
    {
        return $this->passage_reference_info;
    }

    public function setPassageReferenceInfo(string $passage_reference_info): void
    {
        $this->passage_reference_info = $passage_reference_info;
    }

    public function getBookName(): ?string
    {
        return $this->bookName;
    }

    public function setBookName(?string $bookName): void
    {
        $this->bookName = $bookName;
    }

    public function getBookID(): ?string
    {
        return $this->bookID;
    }

    public function setBookID(?string $bookID): void
    {
        $this->bookID = $bookID;
    }

    public function getChapterStart(): ?string
    {
        return $this->chapterStart;
    }

    public function setChapterStart(?string $chapterStart): void
    {
        $this->chapterStart = $chapterStart;
    }

    public function getChapterEnd(): ?string
    {
        return $this->chapterEnd;
    }

    public function setChapterEnd(?string $chapterEnd): void
    {
        $this->chapterEnd = $chapterEnd;
    }

    public function getVerseStart(): ?string
    {
        return $this->verseStart;
    }

    public function setVerseStart(?string $verseStart): void
    {
        $this->verseStart = $verseStart;
    }

    public function getVerseEnd(): ?string
    {
        return $this->verseEnd;
    }

    public function setVerseEnd(?string $verseEnd): void
    {
        $this->verseEnd = $verseEnd;
    }

    public function getPassageID(): ?string
    {
        return $this->passageID;
    }

    public function setPassageID(?string $passageID): void
    {
        $this->passageID = $passageID;
    }

    public function getUversionBookID(): ?string
    {
        return $this->uversionBookID;
    }

    public function setUversionBookID(?string $uversionBookID): void
    {
        $this->uversionBookID = $uversionBookID;
    }

    /**
     * Returns all properties as an associative array.
     *
     * @return array Associative array of all property names and their values.
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

}
