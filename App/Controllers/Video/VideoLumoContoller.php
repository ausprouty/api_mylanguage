<?php
namespace App\Controller\Video;

class VideoLumoController extends Video {

    // input videoCode is 6_529 -GOLUKE
    private function changeVideoLanguage($languageCodeJF){
        $this->videoCode = str_replace('529', $langugeCodeJF, $this->videoCode);
    }
}