<?php

namespace App\Models\BibleStudy;

use ReflectionClass;
use App\Interfaces\ArclightVideoInterface;

final class StudyReferenceModel implements ArclightVideoInterface
{
    protected string $study;
    protected int    $lesson;
    protected string $description;
    protected string $descriptionTwigKey;
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
    protected ?string $videoSource = null;
    protected ?string $videoPrefix = null;
    protected ?string $videoCode = null;
    protected ?string $videoSegment = null;
    protected ?string $startTime = null;
    protected ?string $endTime = null;

    public function __construct()
    {
        $this->study = '';
        $this->lesson = 0;
        $this->bookNumber = 0;
        $this->description = '';
        $this->descriptionTwigKey = '';
        $this->reference = '';
        $this->testament = '';
        $this->passageReferenceInfo = '';
    }

    public function getStudy(): string { return $this->study; }
    public function setStudy(string $study): void { $this->study = $study; }

    public function getLesson(): int { return $this->lesson; }
    public function setLesson(int $lesson): void { $this->lesson = $lesson; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }

    public function getDescriptionTwigKey(): string { return $this->descriptionTwigKey; }
    public function setDescriptionTwigKey(string $key): void { $this->descriptionTwigKey = $key; }

    public function getReference(): string { return $this->reference; }
    public function setReference(string $reference): void { $this->reference = $reference; }

    public function getTestament(): string { return $this->testament; }
    public function setTestament(string $testament): void { $this->testament = $testament; }

    public function getPassageReferenceInfo(): string { return $this->passageReferenceInfo; }
    public function setPassageReferenceInfo(string $info): void { $this->passageReferenceInfo = $info; }

    public function getBookName(): ?string { return $this->bookName; }
    public function setBookName(?string $bookName): void { $this->bookName = $bookName; }

    public function getBookID(): ?string { return $this->bookID; }
    public function setBookID(?string $bookID): void { $this->bookID = $bookID; }

    public function getBookNumber(): int { return $this->bookNumber; }
    public function setBookNumber(int $bookNumber): void { $this->bookNumber = $bookNumber; }

    public function getChapterStart(): ?string { return $this->chapterStart; }
    public function setChapterStart(?string $chapterStart): void { $this->chapterStart = $chapterStart; }

    public function getChapterEnd(): ?string { return $this->chapterEnd; }
    public function setChapterEnd(?string $chapterEnd): void { $this->chapterEnd = $chapterEnd; }

    public function getVerseStart(): ?string { return $this->verseStart; }
    public function setVerseStart(?string $verseStart): void { $this->verseStart = $verseStart; }

    public function getVerseEnd(): ?string { return $this->verseEnd; }
    public function setVerseEnd(?string $verseEnd): void { $this->verseEnd = $verseEnd; }

    public function getPassageID(): ?string { return $this->passageID; }
    public function setPassageID(?string $passageID): void { $this->passageID = $passageID; }

    public function getUversionBookID(): ?string { return $this->uversionBookID; }
    public function setUversionBookID(?string $uversionBookID): void { $this->uversionBookID = $uversionBookID; }

    public function getVideoSource(): ?string { return $this->videoSource; }
    public function setVideoSource(?string $videoSource): void { $this->videoSource = $videoSource; }

    public function getVideoPrefix(): ?string { return $this->videoPrefix; }
    public function setVideoPrefix(?string $videoPrefix): void { $this->videoPrefix = $videoPrefix; }

    public function getVideoCode(): ?string { return $this->videoCode; }
    public function setVideoCode(?string $videoCode): void { $this->videoCode = $videoCode; }

    public function getVideoSegment(): ?string { return $this->videoSegment; }
    public function setVideoSegment(?string $videoSegment): void { $this->videoSegment = $videoSegment; }

    public function getStartTime(): ?string { return $this->startTime; }
    public function setStartTime(?string $startTime): void { $this->startTime = $startTime; }

    public function getEndTime(): ?string { return $this->endTime; }
    public function setEndTime(?string $endTime): void { $this->endTime = $endTime; }

    public function populate(array $data): self
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
        return $this;
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
}
