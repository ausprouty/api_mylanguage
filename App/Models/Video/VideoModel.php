<?php

namespace App\Models\Video;

class VideoModel
{
    private $videoCode;
    private $videoSegment;
    private $videoCodeString;
    private $startTime;
    private $endTime;
    private $languageCodeHL;
    private $languageCodeJF;
    private $template;

    public function __construct($videoCode, $videoSegment = null, $startTime = 0, $endTime = 0, $languageCodeHL = null)
    {
        $this->videoCode = $videoCode;
        $this->videoSegment = $videoSegment;
        $this->startTime = $this->getTimeToSeconds($startTime);
        $this->endTime = $this->getTimeToSeconds($endTime);
        $this->languageCodeHL = $languageCodeHL;
    }

    public function getVideoCode()
    {
        return $this->videoCode;
    }

    public function getLanguageCodeHL()
    {
        return $this->languageCodeHL;
    }

    public function getLanguageCodeJF()
    {
        return $this->languageCodeJF;
    }

    public function setLanguageCodeJF($languageCodeJF)
    {
        $this->languageCodeJF = $languageCodeJF;
    }

    public function getTimeToSeconds($time)
    {
        list($minutes, $seconds) = explode(':', $time);
        return ($minutes * 60) + $seconds;
    }

    public function getVideoSegmentString()
    {
        $segmentString = $this->videoSegment ?? '';
        if ($this->endTime) {
            $segmentString .= '&start=' . $this->startTime;
            $segmentString .= '&end=' . $this->endTime;
        }
        return $segmentString;
    }

    public function setVideoCodeString()
    {
        $this->videoCodeString = $this->videoCode . $this->getVideoSegmentString();
    }

    public function getVideoCodeString()
    {
        return $this->videoCodeString;
    }

    public function loadArclightTemplate()
    {
        $templatePath = ROOT_TEMPLATES . 'videoArclight.twig';
        if (file_exists($templatePath)) {
            $this->template = file_get_contents($templatePath);
        }
    }
}
