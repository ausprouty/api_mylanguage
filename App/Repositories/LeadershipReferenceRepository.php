<?php
namespace App\Repositories;

use App\Models\BibleStudy\LeadershipReferenceModel;
use App\Services\Database\DatabaseService;
use PDO;

class LeadershipReferenceRepository {
    private $databaseService;

    public function __construct(DatabaseService $databaseService) {
        $this->databaseService = $databaseService;
    }

    public function getReferenceByLesson($lesson)
    {
        $query = "SELECT * FROM leadership_references WHERE lesson = :lesson";
        $params = array(':lesson' => $lesson);
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_OBJ);
            if ($data) {
                return new LeadershipReferenceModel($data->lesson, $data->reference, $data->description);
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        return null;
    }
}
?>
