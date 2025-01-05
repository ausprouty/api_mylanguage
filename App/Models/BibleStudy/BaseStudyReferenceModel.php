<?php

namespace App\Models\BibleStudy;

use ReflectionClass;

abstract class BaseStudyReferenceModel
{
    protected int    $lesson;
    protected string $description;
    protected string $description_twig_key;
    protected string $reference;
    protected string $testament;
    protected string $passageReferenceInfo;
    protected ?string $bookName = null;
    protected ?string $bookID = null;
    protected int     $bookNumber;
    protected ?string $chapterStart = null;
    protected ?string $chapterEnd = null;
    protected ?string $verseStart = null;
    protected ?string $verseEnd = null;
    protected ?string $passageID = null;
    protected ?string $uversionBookID = null;

    /**
     * Constructor to initialize default values.
     */
    public function __construct()
    {
        $this->lesson = 0; // Default value for an integer property
        $this->bookNumber = 0;
        $this->description = ''; // Default value for a string property
        $this->descriptionTwigKey = '';
        $this->reference = '';
        $this->testament = '';
        $this->passageReferenceInfo = '';
        // Nullable properties already have default null values
    }

    public function getBookID(): ?string
    {
        return $this->bookID;
    }

    public function getBookName(): ?string
    {
        return $this->bookName;
    }

    public function getBookNumber(): ?string
    {
        return $this->bookNumber;
    }

    public function getChapterEnd(): ?string
    {
        return $this->chapterEnd;
    }

    public function getChapterStart(): ?string
    {
        return $this->chapterStart;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDescriptionTwigKey(): string
    {
        return $this->description_twig_key;
    }

    public function getLesson(): int
    {
        return $this->lesson;
    }

    public function getPassageID(): ?string
    {
        return $this->passageID;
    }

    public function getPassageReferenceInfo(): string
    {
        return $this->passageReferenceInfo;
    }

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

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getTestament(): string
    {
        return $this->testament;
    }

    public function getUversionBookID(): ?string
    {
        return $this->uversionBookID;
    }

    public function getVerseEnd(): ?string
    {
        return $this->verseEnd;
    }

    public function getVerseStart(): ?string
    {
        return $this->verseStart;
    }

    public function populate(array $data): self
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this;
    }

    public function setBookID(?string $bookID): void
    {
        $this->bookID = $bookID;
    }

    public function setBookName(?string $bookName): void
    {
        $this->bookName = $bookName;
    }

    public function setBookNumber(?int $bookNumber): void
    {
        $this->bookNumber = $bookNumber;
    }

    public function setChapterEnd(?string $chapterEnd): void
    {
        $this->chapterEnd = $chapterEnd;
    }

    public function setChapterStart(?string $chapterStart): void
    {
        $this->chapterStart = $chapterStart;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setDescriptionTwigKey(string $description_twig_key): void
    {
        $this->descriptionTwigKey = $description_twig_key;
    }

    public function setLesson(int $lesson): void
    {
        $this->lesson = $lesson;
    }

    public function setPassageID(?string $passageID): void
    {
        $this->passageID = $passageID;
    }

    public function setPassageReferenceInfo(string $passageReferenceInfo): void
    {
        $this->passageReferenceInfo = $passageReferenceInfo;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    public function setTestament(string $testament): void
    {
        $this->testament = $testament;
    }

    public function setUversionBookID(?string $uversionBookID): void
    {
        $this->uversionBookID = $uversionBookID;
    }

    public function setVerseEnd(?string $verseEnd): void
    {
        $this->verseEnd = $verseEnd;
    }

    public function setVerseStart(?string $verseStart): void
    {
        $this->verseStart = $verseStart;
    }
}
