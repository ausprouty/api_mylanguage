<?php
namespace App\Controllers\BibleStudy\Bilingual;

use App\Models\Language\TranslationModel as TranslationModel;

class BilingualTemplateTranslationController {
    
     private $templateName;
     private $template;
     private $translation1;
     private $translation2;
     private $languageCodeHL1;
     private $languageCodeHL2;

     public function __construct($templateName, $translationFile, $languageCodeHL1, $languageCodeHL2){
        $this->templateName = $templateName;
        $this->template = null;
        $this->translationFile = $translationFile;
        $this->languageCodeHL1 = $languageCodeHL1;
        $this->languageCodeHL2 = $languageCodeHL2;
        
        $this->setTemplate();
        $this->setTranslation1();
        $this->setTranslation2();
        $this->replacePlaceHolders();
        
     }
     public function getTemplate(){
        return $this->template;
     }

     private function setTemplate(){
        $filename = ROOT_TEMPLATES . $this->templateName .'.template.html';
        if (!file_exists($filename)){
            writeLogError('BilingualTemplateTranslationController-28', 'ERROR - no such template as ' . $filename);
            return null;
        }
        $this->template = file_get_contents($filename);
     }
     private function setTranslation1(){
      $translationFile = new TranslationModel( $this->languageCodeHL1, $this->translationFile );
      $this->translation1 = $translationFile->getTranslationFile();
     }
     private function setTranslation2(){
      $translationFile = new TranslationModel( $this->languageCodeHL2, $this->translationFile );
      $this->translation2 = $translationFile->getTranslationFile();
     }
     private function replacePlaceholders(){
        foreach ($this->translation1 as $key => $value){
            $find= '{{' . $key . '}}';
            $this->template= str_replace ($find, $value,$this->template);
        }
        foreach ($this->translation2 as $key => $value){
            $find= '||' . $key . '||';
            $this->template= str_replace ($find, $value,$this->template);
        }
     }
}