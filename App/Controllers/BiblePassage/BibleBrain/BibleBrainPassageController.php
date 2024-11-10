<?php

namespace App\Controllers\BiblePassage\BibleBrain;

use App\Services\BibleBrainPassageService;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;

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
