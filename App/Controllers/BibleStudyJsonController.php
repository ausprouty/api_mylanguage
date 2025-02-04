<?php

namespace App\Controllers;

use App\Services\BibleStudy\BiblePassageJsonService;

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

    /**
     * Entry point for web requests. Extracts arguments from the route and delegates to `handleFetch`.
     *
     * @param array $args The route arguments.
     * @return string The fetched study content.
     */
    public function webRequestToFetchBiblePassage(array $args): string {
        // Extract variables from the route arguments
        $study = $args['study'];
        $lesson = (int) $args['lesson'];
        $languageCodeHL = $args['language'];
        $output = $this->studyService->generateBiblePassageJsonBlock( $study,
        $lesson,
        $languageCodeHL);

    }

    
    
}
