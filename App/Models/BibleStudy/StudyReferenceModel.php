<?php
declare(strict_types=1);

namespace App\Models\BibleStudy;

use JsonSerializable;
use App\Support\Caster;
use App\Interfaces\ArclightVideoInterface;

final class StudyReferenceModel implements ArclightVideoInterface, JsonSerializable
{
    // Required strings / ints
    protected string $study = '';
    protected int    $lesson = 0;
    protected string $description = '';
    protected string $descriptionTwigKey = '';
    protected string $reference = '';
    protected string $testament = '';
    protected string $passageReferenceInfo = '';

    // Optional passage fields
    protected ?string $bookName = null;
    protected ?string $bookID = null;
    protected int     $bookNumber = 0;
    protected ?int    $chapterStart = 1;
    protected ?int    $chapterEnd   = 999;
    protected ?int    $verseStart   = 1;
    protected ?int    $verseEnd     = 999;
    protected ?string $passageID = null;
    protected ?string $uversionBookID = null;

    // Video metadata
    protected ?string $videoSource = null;
    protected ?string $videoPrefix = null;
    protected ?string $videoCode = null;
    protected ?string $videoSegment = null;
    protected ?string $startTime = null;
    protected ?string $endTime = null;

    // --- Getters/Setters ---
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

    public function getChapterStart(): ?int { return $this->chapterStart; }
    public function setChapterStart(?int $chapterStart): void { $this->chapterStart = $chapterStart; }

    public function getChapterEnd(): ?int { return $this->chapterEnd; }
    public function setChapterEnd(?int $chapterEnd): void { $this->chapterEnd = $chapterEnd; }

    public function getVerseStart(): ?int { return $this->verseStart; }
    public function setVerseStart(?int $verseStart): void { $this->verseStart = $verseStart; }

    public function getVerseEnd(): ?int { return $this->verseEnd; }
    public function setVerseEnd(?int $verseEnd): void { $this->verseEnd = $verseEnd; }

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

    // --- Hydration ---
    public function populate(array $data): self
    {
        // required strings
        foreach (['study','description','descriptionTwigKey','reference','testament','passageReferenceInfo'] as $k) {
            if (array_key_exists($k, $data)) {
                $this->$k = Caster::toStringNonNull($data[$k]);
            }
        }

        // required ints
        if (array_key_exists('lesson', $data)) {
            $this->lesson = Caster::toNonNegativeInt($data['lesson']);
        }
        if (array_key_exists('bookNumber', $data)) {
            $this->bookNumber = Caster::toNonNegativeInt($data['bookNumber']);
        }

        // optional strings
        foreach (['bookName','bookID','passageID','uversionBookID','videoSource','videoPrefix','videoCode','videoSegment'] as $k) {
            if (array_key_exists($k, $data)) {
                $this->$k = Caster::toNullableString($data[$k]);
            }
        }

        // optional ints
        foreach (['chapterStart','chapterEnd','verseStart','verseEnd'] as $k) {
            if (array_key_exists($k, $data)) {
                $this->$k = Caster::toNullableInt($data[$k]);
            }
        }

        // times
        foreach (['startTime','endTime'] as $k) {
            if (array_key_exists($k, $data)) {
                $this->$k = Caster::normalizeTimeOrNull($data[$k]);
            }
        }

        // light normalization
        if ($this->bookID !== null)         { $this->bookID = strtoupper($this->bookID); }
        if ($this->uversionBookID !== null) { $this->uversionBookID = strtoupper($this->uversionBookID); }
        if ($this->videoSource !== null)    { $this->videoSource = strtolower($this->videoSource); }
        if ($this->testament !== '')        { $this->testament = strtoupper(trim($this->testament)); }

        return $this;
    }

    // --- Serialization ---
    public function toArray(): array
    {
        return [
            'study'               => $this->study,
            'lesson'              => $this->lesson,
            'description'         => $this->description,
            'descriptionTwigKey'  => $this->descriptionTwigKey,
            'reference'           => $this->reference,
            'testament'           => $this->testament,
            'passageReferenceInfo'=> $this->passageReferenceInfo,

            'bookName'       => $this->bookName,
            'bookID'         => $this->bookID,
            'bookNumber'     => $this->bookNumber,
            'chapterStart'   => $this->chapterStart,
            'chapterEnd'     => $this->chapterEnd,
            'verseStart'     => $this->verseStart,
            'verseEnd'       => $this->verseEnd,
            'passageID'      => $this->passageID,
            'uversionBookID' => $this->uversionBookID,

            'videoSource'  => $this->videoSource,
            'videoPrefix'  => $this->videoPrefix,
            'videoCode'    => $this->videoCode,
            'videoSegment' => $this->videoSegment,
            'startTime'    => $this->startTime,
            'endTime'      => $this->endTime,
        ];
    }

    public function jsonSerialize(): array { return $this->toArray(); }

  
   
}
