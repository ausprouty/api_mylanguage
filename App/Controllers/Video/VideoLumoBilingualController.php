<?php
namespace App\Controllers\Video;

class VideoLumoController {

    private $videoCodeString1;
    private $videoCodeString2;


    public function __construct(string $languageCodeHL1, string  $languageCodeHL2, LifePrincipleReference $lifePrincipleReference){
        $this->startTime = $lifePrincipleReference->getStartTime()->getTimeToSeconds();
        $this->endTime = $lifePrincipleReference->getEndTime()->getTimeToSeconds();
        $this->videoCode = $lifePrincipleReference->getVideoCode();
        $this->$videoCodeString1 = $this->setVideoString($languageCodeHL1, $lifePrincipleReference);
        $this->$videoCodeString2 = $this->setVideoString($languageCodeHL2, $lifePrincipleReference);
        echo ( $this->$videoCodeString1  . '<br>');
        echo ( $this->$videoCodeString2  . '<br>');
    }
    private function setVideoString($languageCodeHL,$lifePrincipleReference ){
        $languageCodeJF = getLanguageCodeJF($languageCodeHL);
        $newVideo = changeVideoLanguage($languageCodeJF);
        if ($this->videoExists($newVideo)){
           return $newVideo . $this->getVideoSegmentString();
        }
        else{
            return null;
        }
    }
}