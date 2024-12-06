<?php
namespace App\Controllers\Video;

class VideoLumoController  {

    // input videoCode is 6_529 -GOLUKE
    private function changeVideoLanguage($languageCodeJF){
        $this->videoCode = str_replace('529', $langugeCodeJF, $this->videoCode);
    }
}