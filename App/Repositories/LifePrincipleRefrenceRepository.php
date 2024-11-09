<?php
namespace App\Repositories;

use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Services\Database\DatabaseService;
use PDO;
use Exception;

class LifePrincipleReferenceRepository {
    private $databaseService;

    public function __construct(DatabaseService $databaseService) {
        $this->databaseService = $databaseService;
    }

    public function getReferenceByLesson($lesson)
    {
        $query = "SELECT * FROM life_principle_references WHERE lesson = :lesson";
        $params = array(':lesson' => $lesson);
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_OBJ);
            if ($data) {
                $reference = new LifePrincipleReferenceModel();
                $reference->setLesson($data->lesson);
                $reference->setDescription($data->description);
                $reference->setReference($data->reference);
                $reference->setTestament($data->testament);
                $reference->setQuestion($data->question);
                $reference->setVideoCode($data->videoCode);
                $reference->setVideoSegment($data->videoSegment);
                $reference->setStartTime($data->startTime);
                $reference->setEndTime($data->endTime);
                
                return $reference;
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        return null;
    }
}
?>
