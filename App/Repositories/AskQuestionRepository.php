<?php
namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Models\AskQuestionModel;
use PDO;
use Exception;

class AskQuestionRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getBestSiteByLanguageCodeHL($code)
    {
        $query = "SELECT * FROM ask_questions WHERE languageCodeHL = :code ORDER BY weight DESC LIMIT 1";
        $params = [':code' => $code];

        try {
            $results = $this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_OBJ);
            if ($data) {
                $askQuestion = new AskQuestionModel();
                $askQuestion->setValues($data);
                return $askQuestion;
            }
            return null;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}
