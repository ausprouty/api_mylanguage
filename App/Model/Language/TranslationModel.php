<?php

namespace App\Model\Language;

class TranslationModel
{
  private $translation;

  public function __construct(string $languageCodeHL, string $scope){

    switch ($scope){
        case 'dbs':
            $filename ='dbs.json';
            break;
        case 'leadership':
            $filename ='leadership.json';
            break;
        case 'life':
            $filename ='life.json';
            break;
        case 'video':
            $filename ='video.json';
            break;
        default:
            return;
    }
    $file =  ROOT_TRANSLATIONS .'languages/' . $languageCodeHL .'/'. $filename;
    if (file_exists($file)){
        $text = file_get_contents($file);
        $translation = json_decode($text, true);
    }
    else{
        $file = ROOT_TRANSLATIONS . 'languages/eng00/'. $filename;
        if (file_exists($file)){
            $text = file_get_contents($file);
            $translation = json_decode($text, true);
        }
        else{
            $translation = [];
            $message = ROOT_TRANSLATIONS . 'languages/eng00/'. $filename . " not found";
            trigger_error( $message, E_USER_ERROR);
           
        }
    }
     $this->translation = $translation;
 }
 public function getTranslationFile(){
    return $this->translation;
 }
 public  function translateText($text){
    return $this->translation[$text];
}
}