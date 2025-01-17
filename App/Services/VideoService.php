<?php

namespace App\Services;

use App\Services\TwigService;
use App\Configuration\Config;
use App\Models\Bible\PassageReferenceModel;

class VideoService {

    protected $twigService;

    // Fixed constructor (correct spelling and type hinting)
    public function __construct(TwigService $twigService) {
        $this->twigService = $twigService;
    }

    // Function to get video twig
    public function getVideoBlockIframe(array $translation) {
        $videoInfo = $this->computeParameters ($translation);
        $videoInfo['url'] = Config::get('api.jvideo_player') . $translation['videoCode'];
        $template = 'videoIframe.twig';

        // Ensure TwigService s a render method
        $output = $this->twigService->render($template, $videoInfo);
        print_r($output);
        die;
    }

    public function getUrl(
        PassageReferenceModel $passageReferenceInfo, 
        string $languageCodeJF){
        
        if ($passageReferenceInfo->getVideoSource() == 'arclight'){
            return $this->getArclightUrl($passageReferenceInfo, $languageCodeJF);
        }

    }

    private function getArclightUrl(
        PassageReferenceModel $passageReferenceInfo, 
        string $languageCodeJF): string {
        if (!$languageCodeJF){
            $url = null;
            return $url;
        }
        if ($passageReferenceInfo->getVideoSource() !== 'arclight'){
            $url = null;
            return $url;
        }
        $url = Config::get('api.jvideo_player');
        $url .= $passageReferenceInfo->getVideoPrefix();
        $url .= $languageCodeJF;
        $url .= $passageReferenceInfo->getVideoCode();
      
        $url .= $passageReferenceInfo->getVideoSegment();
        if ($passageReferenceInfo->getEndTime()){
            $seconds = $this->convertMinutesToSeconds($passageReferenceInfo->getStartTime());
            $url .= '&start=' . $seconds;
            $seconds =  $this->convertMinutesToSeconds($passageReferenceInfo->getEndTime());
            $url .= '&end=' . $seconds ;
        }
        $url .= '&playerStyle=default';
        return $url;
    }

    private function computeParameters(array $translation){
        $videoInfo = [];
        $videoInfo['videoCode'] = $translation['videoCode'] ?? '';
        $videoInfo['url'] =  $videoInfo['videoCode'];
        $videoInfo['videoSegment'] =  $translation['videoSegment'];
        $videoInfo['startTime'] = $this->convertMinutesToSeconds($translation['startTime'] ?? '0:00');
        $videoInfo['endTime'] = $this->convertMinutesToSeconds($translation['endTime'] ?? '0:00');
        return $videoInfo;
    }

    // Function to convert time to seconds
    public function convertMinutesToSeconds(string $time): int {
        // Handle invalid input gracefully
        // Some videos use 'start' which should be replaced with 0
        if (!str_contains($time, ':')) {
            return 0;
        }

        // Split the input string into minutes and seconds
        list($minutes, $seconds) = explode(':', $time);

        // Convert minutes and seconds to integers
        $minutes = (int) $minutes;
        $seconds = (int) $seconds;

        // Calculate total seconds
        return ($minutes * 60) + $seconds;
    }
}
