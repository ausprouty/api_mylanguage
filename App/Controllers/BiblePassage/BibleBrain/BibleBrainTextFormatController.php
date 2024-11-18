<?php

namespace App\Controllers\BiblePassage\BibleBrain;

use App\Services\Bible\BibleBrainPassageService;
use App\Models\Bible\BibleReferenceModel;
use App\Models\Bible\BibleModel;

class BibleBrainTextFormatController
{
    private $passageService;
    

    public function __construct(BibleBrainPassageService $passageService)
    {
        $this->passageService = $passageService;
    }

    public function getPassageText( BibleModel $bible, BibleReferenceModel $bibleReference)
    {
        // Fetch and format passage text using the service
        $formattedPassageText = $this->passageService->fetchAndFormatPassage(
            $bible,
            $bibleReference
        );

        return $formattedPassageText;
    }
}
