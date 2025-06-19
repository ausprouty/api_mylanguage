<?php
namespace App\Services\BibleStudy;

//use App\Configuration\Config;
use App\Factories\BibleStudyReferenceFactory;
use App\Services\VideoService;
use App\Services\LoggerService;
use App\Interfaces\ArclightVideoInterface;

class VideoJsonService
{
    protected $bibleStudyReferenceFactory;
    protected $lesson;
    protected $study;
    protected $languageCodeJF;

    public function __construct(
        VideoService $videoService,
        BibleStudyReferenceFactory $bibleStudyReferenceFactory,
        LoggerService $loggerService,
        
    ) {
        $this->loggerService = $loggerService;
        $this->bibleStudyReferenceFactory = $bibleStudyReferenceFactory;
    }
    public function generateVideoJsonBlock(
        $study,
        $lesson,
        $languageCodeJF,
    ): array {
        try {
            $this->study = $study;
            $this->lesson = $lesson;
            $this->languageCodeJF = $languageCodeJF;

            // Create the Bible Study Reference
            $bibleStudyReference = 
                $this->bibleStudyReferenceFactory->createModel(
                $this->study,
                $this->lesson
            );
            $videoUrl = $this->videoService::getArclightUrl(
                $bibleStudyReference, 
                $languageCodeJF);

            // Return the JSON block
            return [
                'videoUrl' => $bibleStudyReference,   
            ];
        } catch (\Exception $e) {
            $this->loggerService->error('Error generating Bible Passage JSON Block: ' . $e->getMessage());
            return [];
        }


