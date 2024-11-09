<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Models\Video\VideoModel;
use PDO;
use Exception;

class VideoRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getLanguageCodeJF($languageCodeHL)
    {
        $query = "SELECT languageCodeJF FROM jesus_video_languages 
                  WHERE languageCodeHL = :languageCodeHL 
                  ORDER BY weight DESC LIMIT 1";
        $params = [':languageCodeHL' => $languageCodeHL];
        
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            return $results->fetch(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    public function getLanguageCodeJFFollowingJesus($languageCodeHL)
    {
        $query = "SELECT languageCodeJF FROM jesus_video_languages 
                  WHERE languageCodeHL = :languageCodeHL 
                  AND title LIKE :following 
                  ORDER BY weight DESC LIMIT 1";
        $params = [
            ':languageCodeHL' => $languageCodeHL,
            ':following' => '%Following Jesus%'
        ];
        
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            return $results->fetch(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

    public function videoExists($videoCode)
    {
        $query = "SELECT videoCode FROM jesus_video_languages WHERE videoCode = :videoCode LIMIT 1";
        $params = [':videoCode' => $videoCode];
        
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            return $results->fetch(PDO::FETCH_COLUMN) !== false;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}
