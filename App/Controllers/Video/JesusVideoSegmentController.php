<?php
namespace App\Controllers\Video;

use App\Services\Database\DatabaseService
use App\Models\Language\TranslationModel as TranslationModel;
use PDO as PDO;
use stdClass as stdClass;

class JesusVideoSegmentController{
    private $data;
    private $formatted;
    private $languageCodeJF;

    public function __construct($languageCodeJF){
        $this->data = null;
        $this->formatted = null;
        $this->languageCodeJF = $languageCodeJF;
    }
       
    public function selectAllSegments(){
        $dbService = new DatabaseService();
        $query = "SELECT * FROM jesus_video_segments
        ORDER BY id";
        try {
            $statement = $dbService->executeQuery($query);
            $this->data = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
  
    private function selectOneSegmentById($id){
        $dbService = new DatabaseService();
        $query = "SELECT * FROM jesus_video_segments
        WHERE id= :id";
        $params = array(':id' => $id);
        try {
            $statement = $dbService->executeQuery($query, $params);
            $this->data = $statement->fetch(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }


    public function formatWithEnglishTitle(){
        $formated = [];
        foreach ($this->data as $segment){
            $title = $segment ['id'] . '. ' . $segment['title']  . ' (' . $segment['verses'] . ')';
            $formatted[] = $this->formatVideoSegment($title, $segment);
        }
        return $formatted;
    }
    
    public function formatWithEthnicTitle($languageCodeHL){
        $formated = [];
        $translation = new TranslationModel($languageCodeHL, 'video');
        foreach ($this->data as $segment){
            $translated = $translation->translateText ($segment['title']);
            $title = $segment ['id'] . '. ' . $translated ;
            $formatted[] = $this->formatVideoSegment($title, $segment);
        }
        return $formatted;
    }
    private function formatVideoSegment($title, $segment){
        $obj =  new stdClass();
        $obj->id = $segment ['id'];
        $obj->title = $title;
        $obj->src = $this->formatVideoSource($segment);
        $obj->videoSegment = $segment['videoSegment'];
        $obj->endTime = $segment['stopTime'];
        return $obj;
    }

      // src="https://api.arclight.org/videoPlayerUrl?refId=1_529-jf6101-0-0&amp;playerStyle=default"
    //returns src="https://api.arclight.org/videoPlayerUrl?refId=6_529-GOJohn2211&amp;start=170&amp;end=229
    protected function formatVideoSource($segment){
        $url = JVIDEO_SOURCE . '1_' . $this->languageCodeJF . '-jf'. $segment['videoSegment'];
        $url .= '&start=0&end=' .  $segment['stopTime'];
        $url .= '&playerStyle=default"';
        return $url;
    }

    
}