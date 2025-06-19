<?php

namespace App\Services\BibleStudy;
use App\Services\BibleStudy\BiblePassageJsonService;
use App\Services\BibleStudy\VideoJsonService;

class LessonPassageJsonService
{
    protected $biblePassageJsonService;
    protected $videoJsonService;            

    public function __construct(
        BiblePassageJsonService $biblePassageJsonService,
        VideoJsonService $videoJsonService
    ) {
        $this->biblePassageJsonService = $biblePassageJsonService;
        $this->videoJsonService = $videoJsonService;
    }
     public function generateLessonPassageJsonBlock(
        $study,
        $lesson,
        $languageCodeHL,
        $languageCodeJF,
    ): array {
        try{
            $bibleOutput = $this->biblePassageJsonService->generateBiblePassageJsonBlock($study, $lesson, $languageCodeHL);
            $videoOutput = $this->videoJsonService->generateVideoJsonBlock($study, $lesson, $languageCodeJF);
            $mergedOutput = array_merge($bibleOutput, $videoOutput);
        }catch (\Exception $e) {
            // Handle the exception, log it, or rethrow it
            throw new \Exception("Error generating Bible passage JSON block: " . $e->getMessage());
        }
}

