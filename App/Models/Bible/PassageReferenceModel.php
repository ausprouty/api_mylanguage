<?php
declare(strict_types=1);

namespace App\Models\Bible;

use JsonSerializable;
use App\Interfaces\ArclightVideoInterface;

class PassageReferenceModel implements ArclightVideoInterface, JsonSerializable
{
    private ?string $entry = null;
    private ?string $bookName = null;
    private ?string $bookID = null;
    private ?string $uversionBookID = null;
    private ?int $bookNumber = null;
    private ?string $testament = null;
    private ?int $chapterStart = null;
    private ?int $verseStart = null;
    private ?int $chapterEnd = null;
    private ?int $verseEnd = null;
    private ?string $passageID = null;

    private ?string $videoSource = null;
    private ?string $videoPrefix = null;
    private ?string $videoCode = null;
    private ?string $videoSegment = null;
    private ?string $startTime = null;
    private ?string $endTime = null;

    public function populate(array $data): self
    {
        foreach ($data as $k => $v) {
            if (\property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
        return $this;
    }

    public function toArray(): array
    {
        return [
            'entry'          => $this->entry,
            'bookName'       => $this->bookName,
            'bookID'         => $this->bookID,
            'uversionBookID' => $this->uversionBookID,
            'bookNumber'     => $this->bookNumber,
            'testament'      => $this->testament,
            'chapterStart'   => $this->chapterStart,
            'verseStart'     => $this->verseStart,
            'chapterEnd'     => $this->chapterEnd,
            'verseEnd'       => $this->verseEnd,
            'passageID'      => $this->passageID,
            'videoSource'    => $this->videoSource,
            'videoPrefix'    => $this->videoPrefix,
            'videoCode'      => $this->videoCode,
            'videoSegment'   => $this->videoSegment,
            'startTime'      => $this->startTime,
            'endTime'        => $this->endTime,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // Getters
    public function getEntry(): ?string { return $this->entry; }
    public function getBookName(): ?string { return $this->bookName; }
    public function getBookID(): ?string { return $this->bookID; }
    public function getUversionBookID(): ?string { return $this->uversionBookID; }
    public function getBookNumber(): ?int { return $this->bookNumber; }
    public function getTestament(): ?string { return $this->testament; }
    public function getChapterStart(): ?int { return $this->chapterStart; }
    public function getVerseStart(): ?int { return $this->verseStart; }
    public function getChapterEnd(): ?int { return $this->chapterEnd; }
    public function getVerseEnd(): ?int { return $this->verseEnd; }
    public function getPassageID(): ?string { return $this->passageID; }
    public function getVideoSource(): ?string { return $this->videoSource; }
    public function getVideoPrefix(): ?string { return $this->videoPrefix; }
    public function getVideoCode(): ?string { return $this->videoCode; }
    public function getVideoSegment(): ?string { return $this->videoSegment; }
    public function getStartTime(): ?string { return $this->startTime; }
    public function getEndTime(): ?string { return $this->endTime; }

    // Setters
    public function setEntry(?string $v): void { $this->entry = $v; }
    public function setBookName(?string $v): void { $this->bookName = $v; }
    public function setBookID(?string $v): void { $this->bookID = $v; }
    public function setUversionBookID(?string $v): void { $this->uversionBookID = $v; }
    public function setBookNumber(?int $v): void { $this->bookNumber = $v; }
    public function setTestament(?string $v): void { $this->testament = $v; }
    public function setChapterStart(?int $v): void { $this->chapterStart = $v; }
    public function setVerseStart(?int $v): void { $this->verseStart = $v; }
    public function setChapterEnd(?int $v): void { $this->chapterEnd = $v; }
    public function setVerseEnd(?int $v): void { $this->verseEnd = $v; }
    public function setPassageID(?string $v): void { $this->passageID = $v; }

    public function setVideoSource(?string $v): void { $this->videoSource = $v; }
    public function setVideoPrefix(?string $v): void { $this->videoPrefix = $v; }
    public function setVideoCode(?string $v): void { $this->videoCode = $v; }
    public function setVideoSegment(?string $v): void { $this->videoSegment = $v; }
    public function setStartTime(?string $v): void { $this->startTime = $v; }
    public function setEndTime(?string $v): void { $this->endTime = $v; }
}
