<?php

namespace App\Controllers\BibleStudy\Bilingual;

use App\Controllers\BibleStudy\LeadershipStudyController;
use App\Models\Language\LanguageModel;
use App\Models\QrCodeGeneratorModel;
use App\Models\BibleStudy\LeadershipReferenceModel;

class BilingualLeadershipTemplateController extends BilingualStudyTemplateController
{
    protected function createQrCode(string $url, string $languageCodeHL): string {
        $size = 240;
        $fileName = 'Leadership' . $this->lesson . '-' . $languageCodeHL . '.png';
        $qrCodeGenerator = new QrCodeGeneratorModel($url, $size, $fileName);
        $qrCodeGenerator->generateQrCode();
        
        return $qrCodeGenerator->getQrCodeUrl();
    }

    public static function findFileName(string $lesson, string $languageCodeHL1, string $languageCodeHL2): string {
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $lang2 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL2);
        $fileName = 'Leadership' . $lesson . '(' . $lang1 . '-' . $lang2 . ')';
        
        return str_replace(' ', '_', trim($fileName));
    }

    public static function findFileNamePdf(string $lesson, string $languageCodeHL1, string $languageCodeHL2): string {
        return self::findFileName($lesson, $languageCodeHL1, $languageCodeHL2) . '.pdf';
    }

    protected function findTitle(string $lesson, string $languageCodeHL1): string {
        return LeadershipStudyController::getTitle($lesson, $languageCodeHL1);
    }

    protected function getBilingualTemplateName(): string {
        return 'bilingualLeadership.template.html';
    }

    public static function getPathPdf(): string {
        return ROOT_RESOURCES . 'pdf/leadership/';
    }

    public static function getUrlPdf(): string {
        return WEBADDRESS_RESOURCES . 'pdf/leadership/';
    }

    public static function getPathView(): string {
        return ROOT_RESOURCES . 'view/leadership/';
    }

    protected function getStudyReferenceInfo(string $lesson): LeadershipReferenceModel {
        $studyReferenceInfo = new LeadershipReferenceModel();
        $studyReferenceInfo->setLesson($lesson);
        
        return $studyReferenceInfo;
    }

    protected function getTranslationSource(): string {
        return 'leadership';
    }

    protected function setFileName(): void {
        $this->fileName = 'Leadership' . $this->lesson . '(' . $this->language1->getName() . '-' . $this->language2->getName() . ')';
        $this->fileName = str_replace(' ', '_', $this->fileName);
    }

    protected function setUniqueTemplateValues(): void {
        // No unique template values for this controller
    }
}
