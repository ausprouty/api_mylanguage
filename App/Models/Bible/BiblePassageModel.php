<?php

namespace App\Models\Bible;

class BiblePassageModel
{
    public $bpid;
    private $referenceLocalLanguage;
    private $passageText;
    private $passageUrl;
    private $dateLastUsed;
    private $dateChecked;
    private $timesUsed;

    public function __construct()
    {
        $this->bpid = '';
        $this->referenceLocalLanguage = '';
        $this->passageText = '';
        $this->passageUrl = '';
        $this->dateLastUsed = null;
        $this->dateChecked = null;
        $this->timesUsed = 0;
    }

    public static function createBiblePassageId(
        string $bid,
        BibleReferenceModel $passage
    ): string {
        return $bid . '-' . $passage->getBookID() . '-' . $passage->getChapterStart() . '-' .
            $passage->getVerseStart() . '-' . $passage->getVerseEnd();
    }
    public function getDateChecked(): ?string
    {
        return $this->dateChecked;
    }
    public function getDateLastUsed(): ?string
    {
        return $this->dateLastUsed;
    }

    public function getPassageText(): string
    {
        return $this->passageText;
    }

    public function getPassageUrl(): string
    {
        return $this->passageUrl;
    }

    public function getReferenceLocalLanguage(): string
    {
        return $this->referenceLocalLanguage;
    }

    public function setDateChecked(?string $date): void
    {
        $this->dateChecked = $date;
    }

    public function setDateLastUsed(?string $date): void
    {
        // Optional: Add validation for the date format
        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new \InvalidArgumentException('Invalid date format');
        }
        $this->dateLastUsed = $date;
    }

    public function setPassageText(string $passageText): void
    {
        $this->passageText = $passageText;
    }

    public function setPassageUrl(string $passageUrl): void
    {
        $this->passageUrl = $passageUrl;
    }

    public function setReferenceLocalLanguage(string $reference): void
    {
        $this->referenceLocalLanguage = $reference;
    }

    public function setTimesUsed(int $times): void
    {
        $this->timesUsed = $times;
    }

    public function updateUsage(): void
    {
        $this->dateLastUsed = date("Y-m-d");
        $this->timesUsed++;
    }
}
