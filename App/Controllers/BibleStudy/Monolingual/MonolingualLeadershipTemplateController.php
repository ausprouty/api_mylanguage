<?php
namespace App\Controller\BibleStudy\Monolingual;

use App\Controllers\BibleStudy\LeadershipStudyController as LeadershipStudyController;
use App\Controllers\BibleStudy\Monolingual\MonolingualStudyTemplateController as MonolingualStudyTemplateController;
use App\Model\Language\LanguageModel as LanguageModel;
use App\Model\QrCodeGeneratorModel as QrCodeGeneratorModel;
use App\Model\BibleStudy\LeadershipReferenceModel as LeadershipReferenceModel;

class MonolingualLeadershipTemplateController extends MonolingualStudyTemplateController
{
    protected function createQrCode($url, $languageCodeHL){
        $size = 240;
        $fileName = 'Leadership'. $this->lesson .'-' .$languageCodeHL . '.png';
        $qrCodeGenerator = new QrCodeGeneratorModel($url, $size, $fileName);
        $qrCodeGenerator->generateQrCode();
        return $qrCodeGenerator->getQrCodeUrl();
    }
    static function findFileName($lesson, $languageCodeHL1){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $fileName =  'Leadership'. $lesson .'('. $lang1 .')';
        $fileName = str_replace( ' ', '_', $fileName);

        return trim($fileName);
    }
    static function findFileNamePdf($lesson, $languageCodeHL1){
        $fileName =  MonolingualLeadershipTemplateController::findFileName($lesson, $languageCodeHL1);
        return $fileName . '.pdf';
    }
    static function findFileNameView($lesson, $languageCodeHL1){
        $fileName =  MonolingualLeadershipTemplateController::findFileName($lesson, $languageCodeHL1);
        return $fileName . '.html';
    }
    protected function findTitle($lesson, $languageCodeHL1){
        return LeadershipStudyController::getTitle($lesson, $languageCodeHL1 );
    }
    protected function getMonolingualPdfTemplateName(){
        return 'monolingualLeadershipPdf.template.html';
    }
    protected function getMonolingualViewTemplateName(){
        return 'monolingualLeadershipView.template.html';
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
    static function getUrlView(){
        return WEBADDRESS_RESOURCES .'view/leadership/';
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
        $this->fileName = 'Leadership' . $this->lesson .'('. $this->language1->getName() . ')';
        $this->fileName = str_replace( ' ', '_', $this->fileName);
    }
    protected function setUniqueTemplateValues(){
   }
}