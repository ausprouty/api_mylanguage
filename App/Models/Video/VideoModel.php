<?php
namespace App\Models\Video;

use App\Services\Database\DatabaseService
use PDO as PDO;

class VideoModel
{


    private $videoCode;
    private $videoSegment;
    private $videoCodeString;
    private $startTime;
    private $endTime;
    private $languageCodeHL;
    private $langaugeCodeJF;
    
    private $template;
    
    public function __construct($videoCode, $videoSegment= null, $startTime = 0, $endTime = 0, $languageCodeHL = null){
        $this->videoCode = $videoCode;
        $this->videoSegment = $videoSegment;
        $this->startTime = $this->getTimeToSeconds($startTime);
        $this->endTime = $this->getTimeToSeconds($endTime);
        $this->languageCodeHL = $languageCodeHL;
        $this->languageCodeJF = $this->getLanguageCodeJF($this->languageCodeHL);
    }
    public function getTimeToSeconds($time) {
        list($minutes, $seconds) = explode(':', $time);
        $totalSeconds = ($minutes * 60) + $seconds;
        return $totalSeconds;
    }
    static function getLanguageCodeJF($languageCodeHL){
        $query = "SELECT languageCodeJF FROM jesus_video_languages 
            WHERE languageCodeHL = :languageCodeHL ORDER BY weight DESC LIMIT 1";
        $params= array(':languageCodeHL' => $languageCodeHL);
        $dbService = new DatabaseService();
        $statement = $dbService->executeQuery($query, $params);
        $languageCodeJF = $statement->fetch(PDO::FETCH_COLUMN);
        return  $languageCodeJF;
    }
    static function getLanguageCodeJFFollowingJesus($languageCodeHL){
        $query = "SELECT languageCodeJF FROM jesus_video_languages 
            WHERE languageCodeHL = :languageCodeHL 
            AND title LIKE :following 
            ORDER BY weight DESC LIMIT 1";
        $params= array(':languageCodeHL' => $languageCodeHL, ':following' => '%Following Jesus%');
        $dbService = new DatabaseService();
        $statement = $dbService->executeQuery($query, $params);
        $languageCodeJF = $statement->fetch(PDO::FETCH_COLUMN);
        return  $languageCodeJF;
    }
    protected function getArclightTemplate(){
        $template = 'videoArclight.template.html';
        $file = ROOT_TEMPLATES . $template;
        if (!file_exists($file)){
            return null;
        }
        $this->template = file_get_contents($file);
    }
    protected function setVideoCodeString(){
        $this->videoCodeString = $this->videoCode;
        $this->videoCodeString .= getVideoSegmentString();
        
    }
    protected function getVideoSegmentString(){
        $videoSegmentString = '';
        if ($this->videoSegment){
            $videoSegmentString .= $this->videoSegment;
        }
        if ($this->endTime){
            $videoSegmentString .= '&start=' . $this->startTime;  
            $videoSegmentString .= '&end=' . $this->endTime;  
        }
        return $videoSegmentString;
    }
    public function getVideoExists($videoCode){
        $query = "SELECT videoCode FROM jesus_video_languages 
            WHERE videoCode = :videoCode LIMIT 1";
        $params= array(':videoCode' => $videoCode);
        $dbService = new DatabaseService();
        $statement = $this->dbConnection->executeQuery($query);
        $videoCode = $statement->fetch(PDO::FETCH_COLUMN);
        return  $videoCode;

    }
}   