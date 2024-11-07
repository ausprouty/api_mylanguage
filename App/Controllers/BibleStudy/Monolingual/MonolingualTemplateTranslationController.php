<?php
namespace App\Controller\BibleStudy\Monolingual;

use App\Model\Language\TranslationModel as TranslationModel;
use App\Model\Language\LanguageModel as LanguageModel;

class MonolingualTemplateTranslationController {
    
     private $templateName;
     private $template;
     private $translation1;
     private $languageCodeHL1;
     private $language1;

     public function __construct($templateName, $translationFile, $languageCodeHL1){
        $this->templateName = $templateName;
        $this->template = null;
        $this->translationFile = $translationFile;
        $this->languageCodeHL1 = $languageCodeHL1;
        $this->setLanguage();
        $this->setTemplate();
        $this->setTranslation1();
        $this->replacePlaceHolders();
        $this->replaceFontHolders();
        
     }
     public function getTemplate(){
        return $this->template;
     }

     private function setLanguage(){
         $this->language1 = new LanguageModel();
         $this->language1->findOneByLanguageCodeHL($this->languageCodeHL1);
     }

     private function setTemplate(){
        $filename = ROOT_TEMPLATES . $this->templateName .'.template.html';
        if (!file_exists($filename)){
            writeLogError('MonolingualTemplateTranslationController-28', 'ERROR - no such template as ' . $filename);
            return null;
        }
        $this->template = file_get_contents($filename);
     }
     private function setTranslation1(){
      $translationFile = new TranslationModel( $this->languageCodeHL1, $this->translationFile );
      $this->translation1 = $translationFile->getTranslationFile();
     }
     private function replacePlaceHolders(){
        foreach ($this->translation1 as $key => $value){
            $find= '{{' . $key . '}}';
            $span = '<span dir="{{dir_language1}}" style="font-family:{{font_language1}};" >';
            $span .= $value . '</span>';
            $this->template = str_replace ($find, $span, $this->template);
        }
     }
     private function replaceFontHolders(){
      $dir = $this->language1->getDirection();
      $this->template = str_replace ('{{dir_language1}}', $dir, $this->template);
      $font = $this->language1->getFont();
      $this->template = str_replace ('{{font_language1}}', $font, $this->template);
     }
}