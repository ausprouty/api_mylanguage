<?php

namespace App\Controllers\BibleStudy\Monolingual;

use App\Controllers\BibleStudy\LifeStudyController;
use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Traits\MonolingualQrCodeTrait;
use App\Traits\MonolingualFileNamingTrait;
use App\Traits\MonolingualTemplatePathsTrait;

class MonolingualLifeTemplateController extends MonolingualStudyTemplateController
{
    use MonolingualQrCodeTrait, MonolingualFileNamingTrait, MonolingualTemplatePathsTrait;

    protected function findTitle(string $lesson, string $languageCodeHL): string {
        return LifeStudyController::getTitle($lesson, $languageCodeHL);
    }

    protected function getMonolingualPdfTemplateName(): string {
        return 'monolingualLifePrinciplesPdf.template.html';
    }

    protected function getMonolingualViewTemplateName(): string {
        return 'monolingualLifePrinciplesView.template.html';
    }

    protected function getStudyReferenceInfo(string $lesson): LifePrincipleReferenceModel {
        $studyReferenceInfo = new LifePrincipleReferenceModel();
        $studyReferenceInfo->setLesson($lesson);
        return $studyReferenceInfo;
    }

    protected function getTranslationSource(): string {
        return 'life';
    }

    protected function setFileName(): void {
        $this->fileName = 'LifePrinciple' . $this->lesson . '(' . $this->language1->getName() . ')';
        $this->fileName = str_replace(' ', '_', $this->fileName);
    }

    protected function setUniqueTemplateValues(): void {
        $question = $this->studyReferenceInfo->getQuestion();
        foreach ($this->translation1 as $key => $value) {
            if ($key == $question) {
                $this->replacePlaceholderWithSpan('{{Topic Sentence}}', $value);
            }
        }
    }

    // Implementations for the trait abstract methods
    protected function getFileNamePrefix(): string {
        return 'LifePrinciple';
    }

    protected static function getPathPrefix(): string {
        return 'principle';
    }
}
