<?php
namespace App\Models\BibleStudy;

use App\Services\Database\DatabaseService;
use PDO as PDO;
//todo  I think this needs a rewrite
class LeadershipReferenceModel {
    protected $databaseService;

    private $lesson;
    public $reference;
    public $description;

    public function __construct(DatabaseService $databaseService, $lesson = null, $reference= null, $description= null) {
        $this->databaseService = $databaseService;
        
        $this->lesson = $lesson;
        $this->reference = $reference;
        $this->description = $description;
    }

    public function setLesson($lesson)
    {
        $query = "SELECT * FROM leadership_references WHERE lesson = :lesson";
        $params = array('lesson'=>$lesson);
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_OBJ);
            if($data){
                $this->lesson =$data->lesson;
                $this->reference= $data->reference;
                $this->description =$data->description;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    public function getEntry(){
        return $this->reference;
    }
    public function getDescription(){
        return $this->description;
    }




}
?>
