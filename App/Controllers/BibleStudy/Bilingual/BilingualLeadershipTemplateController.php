<?php

namespace App\Controllers\BibleStudy\Bilingual;

use App\Models\BibleStudy\LeadershipReferenceModel;
use App\Controllers\BibleStudy\LeadershipStudyController;

/**
 * Class BilingualLeadershipTemplateController
 *
 * Controller for managing Leadership Bible study templates in a bilingual format.
 * Extends BilingualStudyTemplateController to support functionalities specific to Leadership studies.
 *
 * @package App\Controllers\BibleStudy\Bilingual
 */
class BilingualLeadershipTemplateController extends BilingualStudyTemplateController
{
    /**
     * Returns the prefix for filenames specific to Leadership templates.
     *
     * @return string Prefix for Leadership templates.
     */
    protected function getFileNamePrefix(): string {
        return 'Leadership';
    }

    /**
     * Finds and returns the title for a Leadership study based on the lesson and primary language code.
     *
     * @param string $lesson The lesson identifier.
     * @param string $languageCodeHL1 The primary language code for the title.
     * @return string The title of the Leadership study.
     */
    protected function findTitle(string $lesson, string $languageCodeHL1): string {
        return LeadershipStudyController::getTitle($lesson, $languageCodeHL1);
    }

    /**
     * Retrieves the study reference information for a specific Leadership lesson.
     * Uses LeadershipReferenceModel to fetch details for the lesson.
     *
     * @param string $lesson The lesson identifier.
     * @return LeadershipReferenceModel The study reference information for the Leadership study.
     */
    protected function getStudyReferenceInfo(string $lesson): LeadershipReferenceModel {
        $studyReferenceInfo = new LeadershipReferenceModel();
        $studyReferenceInfo->setLesson($lesson);
        return $studyReferenceInfo;
    }

    /**
     * Specifies the translation source for Leadership templates.
     *
     * @return string Translation source identifier for Leadership.
     */
    protected function getTranslationSource(): string {
        return 'leadership';
    }

    /**
     * Sets any unique template values specific to Leadership studies.
     * This method is currently empty as there are no unique values required for Leadership templates.
     */
    protected function setUniqueTemplateValues(): void {
        // No unique template values for this controller
    }
}
