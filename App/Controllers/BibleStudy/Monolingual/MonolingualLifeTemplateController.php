<?php
namespace App\Controllers\BibleStudy\Monolingual;

use App\Controllers\BibleStudy\Monolingual\MonolingualStudyTemplateController as MonolingualStudyTemplateController;
use App\Controllers\BibleStudy\LifeStudyController as LifeStudyController;
use App\Models\Language\LanguageModel as LanguageModel;
use App\Models\QrCodeGeneratorModel as QrCodeGeneratorModel;
use App\Models\BibleStudy\LifePrincipleReferenceModel as LifePrincipleReferenceModel;

class MonolingualLifeTemplateController extends MonolingualStudyTemplateController
{
    protected function createQrCode($url, $languageCodeHL){
        $size = 240;
        $fileName = 'Life'. $this->lesson .'-' .$languageCodeHL . '.png';
        $qrCodeGenerator = new QrCodeGeneratorModel($url, $size, $fileName);
        $qrCodeGenerator->generateQrCode();
        return $qrCodeGenerator->getQrCodeUrl();
    }
    static function findFileName($lesson, $languageCodeHL1){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $fileName =  'LifePrinciple'. $lesson .'('. $lang1 .')';
        $fileName = str_replace( ' ', '_', $fileName);

        return trim($fileName);
    }
    static function findFileNamePdf($lesson, $languageCodeHL1){
        $fileName =  MonolingualLifePdfTemplateController::findFileName($lesson, $languageCodeHL1);
        return $fileName . '.pdf';
    }
    static function findFileNameView($lesson, $languageCodeHL1){
        $fileName =  MonolingualLifeTemplateController::findFileName($lesson, $languageCodeHL1);
        return $fileName . '.html';
    }
    protected function findTitle($lesson, $languageCodeHL1){
        return LifeStudyController::getTitle($lesson, $languageCodeHL1 );
    }
    protected function getMonolingualPdfTemplateName(){
        return 'monolingualLifePrinciplesPdf.template.html';
    }
    protected function getMonolingualViewTemplateName(){
        return 'monolingualLifePrinciplesView.template.html';
    }
    static function getPathPdf(){
        return ROOT_RESOURCES .'pdf/principle/';
    }
    static function getUrlPdf(){
        return WEBADDRESS_RESOURCES .'pdf/principle/';
    }
    static function getPathView(){
        return ROOT_RESOURCES .'view/principle/';
    }
    static function getUrlView(){
        return WEBADDRESS_RESOURCES .'view/principle/';
    }
    protected function getStudyReferenceInfo($lesson){
        $studyReferenceInfo = new LifePrincipleReferenceModel();
        $studyReferenceInfo->setLesson($lesson);
        return $studyReferenceInfo;  
    }
    protected function getTranslationSource(){
        return 'life';
    }
    protected function setFileName(){
        $this->fileName = 'LifePrinciple' . $this->lesson .'('. $this->language1->getName() . ')';
        $this->fileName = str_replace( ' ', '_', $this->fileName);
    }
    protected function setUniqueTemplateValues(){
        $question = $this->studyReferenceInfo->getQuestion();
        $translation1 = $this->getTranslation1();
        foreach ( $translation1 as $key => $value){
            if ($key == $question){
                $this->template= str_replace ('{{Topic Sentence}}', $value, $this->getTemplate());
            }
        }
   }
}