<?php
namespace App\Repositories;

use App\Models\BibleStudy\DbsReferenceModel;
use App\Services\Database\DatabaseService;
use PDO;

class DbsReferenceRepository {
    private $databaseService;

    public function __construct(DatabaseService $databaseService) {
        $this->databaseService = $databaseService;
    }

    public function getReferenceByLesson($lesson)
    {
        $query = "SELECT * FROM dbs_references WHERE lesson = :lesson";
        $params = array(':lesson' => $lesson);
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_OBJ);
            if ($data) {
                return new DbsReferenceModel($data->lesson, $data->reference, $data->description);
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
        }
        return null;
    }
}
?>
