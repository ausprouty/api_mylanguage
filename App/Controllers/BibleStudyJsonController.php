<?php

namespace App\Controllers;

use App\Services\BibleStudy\BiblePassageJsonService;
use App\Responses\JsonResponse;
use App\Services\LoggerService;
use Exception;

class BibleStudyJsonController
{
    /**
     * @var StudyService
     */
    private $studyService;

    /**
     * Constructor for BibleStudyController.
     *
     * @param StudyService $studyService The service responsible for fetching Bible studies.
     */
    public function __construct(BiblePassageJsonService $studyService)
    {
        $this->studyService = $studyService;
    }

    public function lessonBibleContent($study, $lesson, $languageCodeHL): array
    {
        $output = $this->studyService->generateBiblePassageJsonBlock(
            $study,
            $lesson,
            $languageCodeHL
        );
        return $output;
    }


    /**
     * Entry point for web requests. Extracts arguments from the route and delegates to `handleFetch`.
     *
     * @param array $args The route arguments.
     * @return string The fetched study content.
     */
    public function webFetchLessonContent(array $args): void
    {
        try {
            // Validate required arguments
            if (!isset($args['study'], $args['lesson'], $args['languageCodeHL'])) {
                LoggerService::logInfo('BibleStudyJsonController-44', print_r($args, true));
                JsonResponse::error('Missing required arguments: study, lesson, or languageCodeHL');
                return;
            }
            // Extract variables from the route arguments
            $study = $args['study'];
            $lesson = (int) $args['lesson'];
            $languageCodeHL = $args['languageCodeHL'];
            $languageCodeJF = $args['languageCodeJF'] ?? null;

            // Fetch lesson content
            $bibleOutput = $this->lessonBibleContent($study, $lesson, $languageCodeHL);
            $videoOutput = $this->studyService->generateVideoJsonBlock($study, $lesson, $languageCodeJF);
            $mergedOutput = array_merge($bibleOutput, $videoOutput);
            LoggerService::logInfo('BibleStudyJsonController-58', print_r($mergedOutput, true));
            // Return success response
            JsonResponse::success($mergedOutput);
        } catch (Exception $e) {
            // Handle any unexpected errors
            JsonResponse::error($e->getMessage());
        }
    }
}
