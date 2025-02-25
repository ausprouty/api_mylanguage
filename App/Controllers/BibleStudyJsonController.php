<?php

namespace App\Controllers;

use App\Services\BibleStudy\BiblePassageJsonService;
use App\Utilities\JsonResponse;
use Exception;

class BibleStudyJsonController {
    /**
     * @var StudyService
     */
    private $studyService;

    /**
     * Constructor for BibleStudyController.
     *
     * @param StudyService $studyService The service responsible for fetching Bible studies.
     */
    public function __construct(BiblePassageJsonService $studyService) {
        $this->studyService = $studyService;
    }

    public function lessonContent($study, $lesson, $languageCodeHL ): array{
        $output = $this->studyService->generateBiblePassageJsonBlock( $study,
        $lesson,
        $languageCodeHL);
        return $output;

    }

    /**
     * Entry point for web requests. Extracts arguments from the route and delegates to `handleFetch`.
     *
     * @param array $args The route arguments.
     * @return string The fetched study content.
     */
    public function webFetchLessonContent(array $args): void {
        try {
            // Validate required arguments
            if (!isset($args['study'], $args['lesson'], $args['languageCodeHL'])) {
                JsonResponse::error('Missing required arguments: study, lesson, or languageCodeHL');
                return;
            }
    
            // Extract variables from the route arguments
            $study = $args['study'];
            $lesson = (int) $args['lesson'];
            $languageCodeHL = $args['languageCodeHL'];
    
            // Fetch lesson content
            $output = $this->lessonContent($study, $lesson, $languageCodeHL);
    
            // Return success response
            JsonResponse::success($output);
        } catch (Exception $e) {
            // Handle any unexpected errors
            JsonResponse::error($e->getMessage());
        }
    }
    


    
    
}
