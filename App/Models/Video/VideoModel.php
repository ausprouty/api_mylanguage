<?php
namespace App\Models\Video;

Use App\Configuration\Config;

class VideoModel
{
    private $videoSource;
    private $videoPrefix;
    private $videoCode;
    private $videoSegment;
    private $startTime;
    private $endTime;
    private $arclightUrl;
    private $languageCodeHL;
    private $languageCodeJF;

    public function __construct(array $data)
    {
        $this->videoSource = $data['videoSource'] ?? null;
        $this->videoPrefix = $data['videoPrefix'] ?? null;
        $this->videoCode = $data['videoCode'] ?? null;
        $this->videoSegment = $data['videoSegment'] ?? null;
        $this->startTime = $this->getTimeToSeconds($data['startTime'] ?? 0);
        $this->endTime = $this->getTimeToSeconds($data['endTime'] ?? 0);
        $this->languageCodeHL = $data['languageCodeHL'] ?? null;
        $this->languageCodeJF = $data['languageCodeJF'] ?? null;
    }

    public function getVideoCode(): ?string
    {
        return $this->videoCode;
    }

    public function getLanguageCodeHL(): ?string
    {
        return $this->languageCodeHL;
    }

    public function getLanguageCodeJF(): ?string
    {
        return $this->languageCodeJF;
    }

    public function setLanguageCodeJF(string $languageCodeJF): void
    {
        $this->languageCodeJF = $languageCodeJF;
    }

    public function getTimeToSeconds($time): int
    {
        if (is_int($time)) {
            return $time;
        }

        if (strpos($time, ':') !== false) {
            list($minutes, $seconds) = explode(':', $time);
            return ($minutes * 60) + $seconds;
        }
        //will return 0 if time is set to 'start'

        return 0;
    }

    public function getVideoSegmentString(): string
    {
        $segmentString = $this->videoSegment ?? '';
        if ($this->endTime) {
            $segmentString .= "&start={$this->startTime}";
            $segmentString .= "&end={$this->endTime}";
        }
        return $segmentString;
    }

    public function setArclightUrl(): void
    {
        if (!$this->languageCodeJF){
            $this->arclightUrl = null;
            return;
        }
        if ($this->videoSource !== 'arclight'){
            $this->arclightUrl = null;
            return;
        }
        $this->arclightUrl = Config::get('api.jvideo_player');
        $this->arclightUrl .= $this->videoPrefix;
        $this->arclightUrl .= $this->videoCode;
        $this->arclightUrl .= $this->languageCodeHL;
        $this->arclightUrl .= $this->videoSegment;
        if ($this->endTime){
            $this->arclightUrl .= '&start=' . $this->startTime;
            $this->arclightUrl .= '&end=' . $this->endTime;
        }
    }

    public function getArclightUrl(): ?string
    {
        return $this->arclightUrl;
    }

    public static function createFromStudyModel(array $studyModelData, string $languageCodeJF): self
    {
        return new self([
            'videoSource' => $studyModelData['videoSource'] ?? null,
            'videoPrefix' => $studyModelData['videoPrefix'] ?? null,
            'videoCode' => $studyModelData['videoCode'] ?? null,
            'videoSegment' => $studyModelData['videoSegment'] ?? null,
            'startTime' => $studyModelData['startTime'] ?? 0,
            'endTime' => $studyModelData['endTime'] ?? 0,
            'languageCodeJF' => $languageCodeJF ?? null,
        ]);
    }

    public static function createFromDatabase(array $dbData, string $languageCodeJF): self
    {
        return new self([
            'videoSource' => $dbData['videoSource'] ?? null,
            'videoPrefix' => $dbData['videoPrefix'] ?? null,
            'videoCode' => $dbData['videoCode'] ?? null,
            'videoSegment' => $dbData['segment'] ?? null,
            'startTime' => $dbData['startTime'] ?? 0,
            'endTime' => $dbData['endTime'] ?? 0,
            
            'languageCodeJF' => $languageCodeJF ?? null,
        ]);
    }
}
