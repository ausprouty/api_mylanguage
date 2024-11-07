<?php
namespace App\Controllers\BibleStudy\Bilingual;

use App\Controllers\BibleStudy\Bilingual\BilingualStudyTemplateController as BilingualStudyTemplateController;
use App\Controllers\BibleStudy\LeadershipStudyController as LeadershipStudyController;
use App\Model\Language\LanguageModel as LanguageModel;
use App\Model\QrCodeGeneratorModel as QrCodeGeneratorModel;
use App\Model\BibleStudy\LeadershipReferenceModel as LeadershipReferenceModel;

class BilingualLeadershipTemplateController extends BilingualStudyTemplateController
{
    protected function createQrCode($url, $languageCodeHL){
        $size = 240;
        $fileName = 'Leadership'. $this->lesson .'-' .$languageCodeHL . '.png';
        $qrCodeGenerator = new QrCodeGeneratorModel($url, $size, $fileName);
        $qrCodeGenerator->generateQrCode();
        return $qrCodeGenerator->getQrCodeUrl();
    }
    static function findFileName($lesson, $languageCodeHL1, $languageCodeHL2){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $lang2 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL2);
        $fileName =  'Leadership'. $lesson .'('. $lang1 . '-' . $lang2 .')';
        $fileName = str_replace( ' ', '_', $fileName);

        return trim($fileName);
    }
    static function findFileNamePdf($lesson, $languageCodeHL1, $languageCodeHL2){
        $fileName =  BilingualLeadershipTemplateController::findFileName($lesson, $languageCodeHL1, $languageCodeHL2);
        return $fileName . '.pdf';
    }
    protected function findTitle($lesson, $languageCodeHL1){
        return LeadershipStudyController::getTitle($lesson,$languageCodeHL1);
    }
    protected function getBilingualTemplateName(){
        return 'bilingualLeadership.template.html';
    }
    static function getPathPdf(){
        return ROOT_RESOURCES .'pdf/leadership/';
    }
    static function getUrlPdf(){
        return WEBADDRESS_RESOURCES .'pdf/leadership/';
    }
    static function getPathView(){
        return ROOT_RESOURCES .'view/leadership/';
    }
    protected function getStudyReferenceInfo($lesson){
        $studyReferenceInfo = new LeadershipReferenceModel();
        $studyReferenceInfo->setLesson($lesson);
        return $studyReferenceInfo;  
    }
    protected function getTranslationSource(){
        return 'leadership';
    }
    protected function setFileName(){
        $this->fileName = 'Leadership' . $this->lesson .'('. $this->language1->getName()  .'-'. $this->language2->getName() . ')';
    }
    protected function setUniqueTemplateValues(){
        return;
    }
}