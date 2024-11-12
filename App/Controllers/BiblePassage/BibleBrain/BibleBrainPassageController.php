<?php

namespace App\Controllers\BiblePassage\BibleBrain;

use App\Services\Bible\BibleBrainPassageService;


class BibleBrainPassageController
{
    private $passageService;

    public function __construct(BibleBrainPassageService $passageService)
    {
        $this->passageService = $passageService;
    }

    public function getBiblePassage($languageCodeIso, $bibleReferenceInfo)
    {
        return $this->passageService->fetchAndFormatPassage($languageCodeIso, $bibleReferenceInfo);
    }
}
