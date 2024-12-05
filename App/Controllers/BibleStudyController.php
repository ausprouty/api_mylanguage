<?php

namespace App\Controllers;

use App\Services\StudyService;

class BibleStudyController {
    /**
     * @var StudyService
     */
    private $studyService;

    /**
     * Constructor for BibleStudyController.
     *
     * @param StudyService $studyService The service responsible for fetching Bible studies.
     */
    public function __construct(StudyService $studyService) {
        $this->studyService = $studyService;
    }

    /**
     * Fetch a Bible study based on study details.
     *
     * @param string      $study     The name of the study.
     * @param string      $format    The format (e.g., 'html', 'pdf').
     * @param int         $session   The session number.
     * @param string      $language1 The primary language of the study.
     * @param string|null $language2 Optional secondary language for bilingual studies.
     *
     * @return string The fetched study content.
     */
    public function fetchStudy(
        string $study,
        string $format,
        int $session,
        string $language1,
        ?string $language2 = null
    ): string {
        // Delegate the work to the StudyService with the correct parameter order
        return $this->studyService->fetchStudy($study, $format, $session, $language1, $language2);
    }
}
