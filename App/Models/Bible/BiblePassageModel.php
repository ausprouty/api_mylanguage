<?php

namespace App\Models\Bible;

use App\Models\Bible\BibleReferenceInfoModel;

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

    // Getters and Setters
    public function getPassageText(): string
    {
        return $this->passageText;
    }

    public function setPassageText(string $passageText): void
    {
        $this->passageText = $passageText;
    }

    public function getPassageUrl(): string
    {
        return $this->passageUrl;
    }

    public function setPassageUrl(string $passageUrl): void
    {
        $this->passageUrl = $passageUrl;
    }

    public function getReferenceLocalLanguage(): string
    {
        return $this->referenceLocalLanguage;
    }

    public function setReferenceLocalLanguage(string $reference): void
    {
        $this->referenceLocalLanguage = $reference;
    }

    public function updateUsage(): void
    {
        $this->dateLastUsed = date("Y-m-d");
        $this->timesUsed++;
    }

    public static function createBiblePassageId(
        string $bid, 
        BibleReferenceInfoModel $passage
        ): string
    {
        return $bid . '-' . $passage->getBookID() . '-' . $passage->getChapterStart() . '-' .
            $passage->getVerseStart() . '-' . $passage->getVerseEnd();
    }

    public function populateFromData($data): void
    {
        $this->bpid = $data->bpid;
        $this->referenceLocalLanguage = $data->referenceLocalLanguage;
        $this->passageText = $data->passageText;
        $this->passageUrl = $data->passageUrl;
        $this->dateLastUsed = $data->dateLastUsed;
        $this->dateChecked = $data->dateChecked;
        $this->timesUsed = $data->timesUsed;
    }
}
