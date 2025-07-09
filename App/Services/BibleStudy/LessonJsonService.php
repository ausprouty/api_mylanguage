<?php

namespace App\Services\BibleStudy;

use App\Services\BibleStudy\BiblePassageJsonService;
use App\Services\BibleStudy\VideoJsonService;

class LessonJsonService
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

    public function generateLessonJsonObject(
        $study,
        $lesson,
        $languageCodeHL,
        $languageCodeJF // ✅ removed trailing comma
    ): array {
        try {
            $bibleOutput = $this->biblePassageJsonService->generateBiblePassageJsonBlock($study, $lesson, $languageCodeHL);
            $videoOutput = $this->videoJsonService->generateVideoJsonBlock($study, $lesson, $languageCodeJF);
            $mergedOutput = array_merge($bibleOutput, $videoOutput);

            return $mergedOutput; // ✅ added return
        } catch (\Exception $e) {
            throw new \Exception("Error generating Bible passage JSON block: " . $e->getMessage());
        }
    }
}
