<?php

namespace App\Controllers\BiblePassage\BibleBrain;

use App\Services\Bible\BibleBrainPassageService;
use App\Models\Bible\BibleReferenceInfoModel;

class BibleBrainTextFormatController
{
    private $passageService;

    public function __construct(BibleBrainPassageService $passageService)
    {
        $this->passageService = $passageService;
    }

    public function getPassageText(BibleReferenceInfoModel $bibleReferenceInfo)
    {
        // Fetch and format passage text using the service
        $formattedPassageText = $this->passageService->fetchAndFormatPassage(
            $bibleReferenceInfo->getLanguageCodeIso(), 
            $bibleReferenceInfo
        );
        
        return $formattedPassageText;
    }
}
