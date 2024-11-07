<?php
namespace App\Controller\BibleStudy\Monolingual;

use App\Controller\BibleStudy\DbsStudyController as  DbsStudyController;
use App\Controller\BibleStudy\Monolingual\MonolingualStudyTemplateController as MonolingualStudyTemplateController;
use App\Model\Language\LanguageModel as LanguageModel;
use App\Model\BibleStudy\DbsReferenceModel as DbsReferenceModel;
use App\Model\QrCodeGeneratorModel as QrCodeGeneratorModel;

class MonolingualDbsTemplateController extends MonolingualStudyTemplateController
{
    protected function createQrCode($url, $languageCodeHL){
        $size = 240;
        $fileName = 'DBS'. $this->lesson .'-' .$languageCodeHL . '.png';
        $qrCodeGenerator = new QrCodeGeneratorModel($url, $size, $fileName);
        $qrCodeGenerator->generateQrCode();
        return $qrCodeGenerator->getQrCodeUrl();
    }
    static function findFileName($lesson, $languageCodeHL1){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $fileName =  'DBS'. $lesson .'('. $lang1 .')';
        $fileName = str_replace( ' ', '_', $fileName);

        return trim($fileName);
    }
    static function findFileNamePdf($lesson, $languageCodeHL1){
        $fileName =  MonolingualDbsTemplateController::findFileName($lesson, $languageCodeHL1);
        return $fileName . '.pdf';
    }
    static function findFileNameView($lesson, $languageCodeHL1){
        $fileName =  MonolingualDbsTemplateController::findFileName($lesson, $languageCodeHL1);
        return $fileName . '.html';
    }
    protected function findTitle($lesson, $languageCodeHL1){
        return DbsStudyController::getTitle($lesson,$languageCodeHL1 );
    }
    protected function getMonolingualPdfTemplateName(){
        return 'monolingualDbsPdf.template.html';
    }
    protected function getMonolingualViewTemplateName(){
        return 'monolingualDbsView.template.html';
    }
    static function getPathPdf(){
        return ROOT_RESOURCES .'pdf/dbs/';
    }
    static function getUrlPdf(){
        return WEBADDRESS_RESOURCES .'pdf/dbs/';
    }
    static function getPathView(){
        return ROOT_RESOURCES .'view/dbs/';
    }
    static function getUrlView(){
        return WEBADDRESS_RESOURCES .'view/dbs/';
    }
    protected function getStudyReferenceInfo($lesson){
        $studyReferenceInfo = new DbsReferenceModel();
        $studyReferenceInfo->setLesson($lesson);
        return $studyReferenceInfo;  
    }
    protected function getTranslationSource(){
        return 'dbs';
    }
    protected function setFileName(){
        $this->fileName = 'DBS' . $this->lesson .'('. $this->language1->getName() . ')';
        $this->fileName = str_replace( ' ', '_', $this->fileName);
    }
    protected function setUniqueTemplateValues(){
        
   }
}