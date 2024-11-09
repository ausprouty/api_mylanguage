<?php

namespace App\Models\Bible;

use App\Models\Bible\BibleReferenceInfoModel;

class BiblePassageModel
{
    public $bpid;
    protected $referenceLocalLanguage;
    protected $passageText;
    protected $passageUrl;
    protected $dateLastUsed;
    protected $dateChecked;
    protected $timesUsed;

    public function __construct()
    {
        $this->bpid = '';
        $this->referenceLocalLanguage = '';
        $this->passageText = '';
        $this->passageUrl = '';
        $this->dateLastUsed = '';
        $this->dateChecked = '';
        $this->timesUsed = 0;
    }

    // Getters
    public function getPassageText()
    {
        return $this->passageText;
    }

    public function getPassageUrl()
    {
        return $this->passageUrl;
    }

    public function getReferenceLocalLanguage()
    {
        return $this->referenceLocalLanguage;
    }

    // Method to create a Bible Passage ID based on given parameters
   static public function createBiblePassageId(string $bid, BibleReferenceInfoModel $passage): string
    {
        return $bid . '-' . $passage->getBookID() . '-' . $passage->getChapterStart() . '-' .
            $passage->getVerseStart() . '-' . $passage->getVerseEnd();
    }

    // Method to populate the model with data from the database
    public function populateFromData($data)
    {
        $this->bpid = $data->bpid;
        $this->referenceLocalLanguage = $data->referenceLocalLanguage;
        $this->passageText = $data->passageText;
        $this->passageUrl = $data->passageUrl;
        $this->dateLastUsed = $data->dateLastUsed;
        $this->dateChecked = $data->dateChecked;
        $this->timesUsed = $data->timesUsed;
    }

    // Method to update usage date and increment times used
    public function updateUsage()
    {
        $this->dateLastUsed = date("Y-m-d");
        $this->timesUsed += 1;
    }
}
