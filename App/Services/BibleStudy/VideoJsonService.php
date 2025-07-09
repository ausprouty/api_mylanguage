<?php

namespace App\Services\BibleStudy;

use App\Factories\BibleStudyReferenceFactory;
use App\Services\VideoService;
use App\Services\LoggerService;

class VideoJsonService
{
    protected $videoService;
    protected $bibleStudyReferenceFactory;
    protected $loggerService;

    public function __construct(
        VideoService $videoService,
        BibleStudyReferenceFactory $bibleStudyReferenceFactory,
        LoggerService $loggerService
    ) {
        $this->videoService = $videoService;
        $this->bibleStudyReferenceFactory = $bibleStudyReferenceFactory;
        $this->loggerService = $loggerService;
    }

    public function generateVideoJsonBlock(
        string $study,
        int $lesson,
        string $languageCodeJF
    ): array {
        try {
            $bibleStudyReference = $this->bibleStudyReferenceFactory->createModel(
                $study,
                $lesson
            );

            $videoUrl = $this->videoService::getArclightUrl(
                $bibleStudyReference,
                $languageCodeJF
            );

            return [
                'videoUrl' => $videoUrl,
            ];
        } catch (\Exception $e) {
            $this->loggerService::logError('VideoJsonService-5', 
                'Error generating video JSON block: ' . $e->getMessage());
            return [];
        }
    }
}
