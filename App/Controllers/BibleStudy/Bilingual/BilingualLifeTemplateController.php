<?php

namespace App\Controllers\BibleStudy\Bilingual;

use App\Controllers\BibleStudy\LifeStudyController;
use App\Models\Language\LanguageModel;
use App\Models\QrCodeGeneratorModel;
use App\Models\BibleStudy\LifePrincipleReferenceModel;

class BilingualLifeTemplateController extends BilingualStudyTemplateController
{
    protected function createQrCode(string $url, string $languageCodeHL): string {
        $size = 240;
        $fileName = 'Life' . $this->lesson . '-' . $languageCodeHL . '.png';
        $qrCodeGenerator = new QrCodeGeneratorModel($url, $size, $fileName);
        $qrCodeGenerator->generateQrCode();
        
        return $qrCodeGenerator->getQrCodeUrl();
    }

    public static function findFileName(string $lesson, string $languageCodeHL1, string $languageCodeHL2): string {
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $lang2 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL2);
        $fileName = 'LifePrinciple' . $lesson . '(' . $lang1 . '-' . $lang2 . ')';
        
        return str_replace(' ', '_', trim($fileName));
    }

    public static function findFileNamePdf(string $lesson, string $languageCodeHL1, string $languageCodeHL2): string {
        return self::findFileName($lesson, $languageCodeHL1, $languageCodeHL2) . '.pdf';
    }

    protected function findTitle(string $lesson, string $languageCodeHL1): string {
        return LifeStudyController::getTitle($lesson, $languageCodeHL1);
    }

    protected function getBilingualTemplateName(): string {
        return 'bilingualLifePrinciples.template.html';
    }

    public static function getPathPdf(): string {
        return ROOT_RESOURCES . 'pdf/principle/';
    }

    public static function getUrlPdf(): string {
        return WEBADDRESS_RESOURCES . 'pdf/principle/';
    }

    public static function getPathView(): string {
        return ROOT_RESOURCES . 'view/principle/';
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
        $this->fileName = 'LifePrinciple' . $this->lesson . '(' . $this->language1->getName() . '-' . $this->language2->getName() . ')';
        $this->fileName = str_replace(' ', '_', $this->fileName);
    }

    protected function setUniqueTemplateValues(): void {
        $question = $this->studyReferenceInfo->getQuestion();
        
        $this->replaceTemplateValues('{{Topic Sentence}}', $this->getTranslation1(), $question);
        $this->replaceTemplateValues('||Topic Sentence||', $this->getTranslation2(), $question);
    }

    /**
     * Replaces placeholders for a specific question in the template
     *
     * @param string $placeholder Placeholder in the template (e.g., '{{Topic Sentence}}')
     * @param array $translations Array of translations to search for the question key
     * @param string $question Question key to search for in the translations
     */
    private function replaceTemplateValues(string $placeholder, array $translations, string $question): void {
        if (array_key_exists($question, $translations)) {
            $this->template = str_replace($placeholder, $translations[$question], $this->template);
        }
    }
}
