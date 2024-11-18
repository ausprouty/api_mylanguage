<?php

namespace App\Factories;

use App\Models\Bible\BiblePassageModel;

class BiblePassageFactory
{
    public static function createFromData($data): BiblePassageModel
{
    $biblePassage = new BiblePassageModel();
    $biblePassage->bpid = $data->bpid;
    $biblePassage->setReferenceLocalLanguage($data->referenceLocalLanguage);
    $biblePassage->setPassageText($data->passageText);
    $biblePassage->setPassageUrl($data->passageUrl);
    $biblePassage->setDateLastUsed($data->dateLastUsed); // Use setter
    $biblePassage->setDateChecked($data->dateChecked);   // Use setter
    $biblePassage->setTimesUsed($data->timesUsed);       // Use setter

    return $biblePassage;
}

}
