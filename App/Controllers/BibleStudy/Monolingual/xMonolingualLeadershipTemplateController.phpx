<?php

namespace App\Controllers\BibleStudy\Monolingual;

use App\Controllers\BibleStudy\LeadershipStudyController;
use App\Models\BibleStudy\LeadershipReferenceModel;


class MonolingualLeadershipTemplateController extends MonolingualStudyTemplateController
{


    protected function findTitle(string $lesson, string $languageCodeHL): string
    {
        return LeadershipStudyController::getTitle($lesson, $languageCodeHL);
    }

    protected function getMonolingualPdfTemplateName(): string
    {
        return 'monolingualLeadershipPdf.twig';
    }

    protected function getMonolingualViewTemplateName(): string
    {
        return 'monolingualLeadershipView.twig';
    }

    protected function getStudyReferenceInfo(string $lesson): LeadershipReferenceModel
    {
        $studyReferenceInfo = new LeadershipReferenceModel();
        $studyReferenceInfo->setLesson($lesson);
        return $studyReferenceInfo;
    }

    protected function getTranslationSource(): string
    {
        return 'leadership';
    }

    protected function setFileName(): void
    {
        $this->fileName = 'Leadership' . $this->lesson . '(' . $this->language1->getName() . ')';
        $this->fileName = str_replace(' ', '_', $this->fileName);
    }

    protected function setUniqueTemplateValues(): void
    {
        // Add any leadership-specific values
    }

    protected function getFileNamePrefix(): string
    {
        return 'Leadership';
    }

    protected static function getPathPrefix(): string
    {
        return 'leadership';
    }
}
