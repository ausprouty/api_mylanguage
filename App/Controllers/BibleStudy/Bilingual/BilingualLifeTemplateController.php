<?php

namespace App\Controllers\BibleStudy\Bilingual;

use App\Controllers\BibleStudy\Bilingual\BilingualStudyTemplateController as BilingualStudyTemplateController;
use App\Controllers\BibleStudy\LifeStudyController as LifeStudyController;
use App\Models\Language\LanguageModel as LanguageModel;
use App\Models\QrCodeGeneratorModel as QrCodeGeneratorModel;
use App\Models\BibleStudy\LifePrincipleReferenceModel AS LifePrincipleReferenceModel;

class BilingualLifeTemplateController extends BilingualStudyTemplateController
{
    protected function createQrCode($url, $languageCodeHL){
        $size = 240;
        $fileName = 'Life'. $this->lesson .'-' .$languageCodeHL . '.png';
        $qrCodeGenerator = new QrCodeGeneratorModel($url, $size, $fileName);
        $qrCodeGenerator->generateQrCode();
        return $qrCodeGenerator->getQrCodeUrl();
    }
    static function findFileName($lesson, $languageCodeHL1, $languageCodeHL2){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $lang2 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL2);
        $fileName =  'LifePrinciple'. $lesson .'('. $lang1 . '-' . $lang2 .')';
        $fileName = str_replace( ' ', '_', $fileName);

        return trim($fileName);
    }
    static function findFileNamePdf($lesson, $languageCodeHL1, $languageCodeHL2){
        $fileName =  BilingualLifeTemplateController::findFileName($lesson, $languageCodeHL1, $languageCodeHL2);
        return $fileName . '.pdf';
    }
    protected function findTitle($lesson, $languageCodeHL1){
        return LifeStudyController::getTitle($lesson, $languageCodeHL1);
    }
    protected function getBilingualTemplateName(){
        return 'bilingualLifePrinciples.template.html';
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
    protected function getStudyReferenceInfo($lesson){
        $studyReferenceInfo = new LifePrincipleReferenceModel();
        $studyReferenceInfo->setLesson($lesson);
        return $studyReferenceInfo;  
    }
    protected function getTranslationSource(){
        return 'life';
    }
    protected function setFileName(){
        $this->fileName = 'LifePrinciple' . $this->lesson .'('. $this->language1->getName()  .'-'. $this->language2->getName() . ')';
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
        $translation2 = $this->getTranslation2();
        foreach ($translation2  as $key => $value){
            if ($key == $question){
                $this->template= str_replace ('||Topic Sentence||', $value, $this->template);
            }
        }
   }
}