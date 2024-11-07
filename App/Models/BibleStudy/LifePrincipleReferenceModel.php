<?php
namespace App\Models\BibleStudy;

use App\Services\Database\DatabaseService;
use PDO as PDO;
//todo  I think this needs a rewrite
class LifePrincipleReferenceModel {
    protected $databaseService;

    private $lesson;
    private $description;
    private $reference;
    private $testament;
    private $question;
    private $videoCode;
    private $videoSegment;
    private $startTime;
    private $endTime;

    public function __construct(DatabaseService $databaseService, $lesson = null, $reference= null, $description= null) {
        $this->databaseService = $databaseService;
        
        $this->lesson = $lesson;
        $this->reference = $reference;
        $this->description = $description;
    }

    public function setLesson($lesson)
    {
        $query = "SELECT * FROM life_principle_references WHERE lesson = :lesson";
        $params = array('lesson'=>$lesson);
        try {
            $statement = $this->dbConnection->executeQuery($query, $params);
            $data = $statement->fetch(PDO::FETCH_OBJ);
            if($data){
                $this->lesson =$data->lesson;
                $this->description =$data->description;
                $this->reference= $data->reference;
                $this->testament = $data->testament;
                $this->question = $data->question;
                $this->videoCode = $data->videoCode;
                $this->videoSegment = $data->videoSegment;
                $this->startTime = $data->startTime;
                $this->endTime = $data->endTime;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    public function getDescription(){
        return $this->description;
    }
    public function getEntry(){
        return $this->reference;
    }
    public function getTestament(){
        return $this->testament;
    }
    public function getQuestion(){
        return $this->question;
    }
    public function getVideoCode(){
        return $this->videoCode;
    }
    public function getVideoSegment(){
        return $this->videoSegment;
    }
    public function getStartTime(){
        return $this->startTime;
    }
    public function getendTime(){
        return $this->endTime;
    }
   

}
?>
