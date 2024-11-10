<?php
namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Models\AskQuestionModel;
use PDO;

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

        // Fetch a single row using fetchRow, which returns null if no result is found
        $data = $this->databaseService->fetchRow($query, $params);

        if ($data) {
            $askQuestion = new AskQuestionModel();
            $askQuestion->setValues($data);
            return $askQuestion;
        }

        return null;
    }
}
