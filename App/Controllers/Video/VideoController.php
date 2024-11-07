<?php
namespace App\Controllers\Video;

use App\Models\Data\DatabaseConnectionModel as DatabaseConnectionModel;
use PDO as PDO;

class VideoController extends Video {

    // input videoCode is 6_529 -GOLUKE
    private function changeVideoLanguage($languageCodeJF){
        $this->videoCode = str_replace('529', $langugeCodeJF, $this->videoCode);
    }

    static function getVideoCodeFromTitle($title, $languageCodeHL){
        $title = str_ireplace('%20', ' ', $title);
        $dbConnection = new DatabaseConnectionModel();
        $query = "SELECT videoCode FROM jesus_video_languages
            WHERE title = :title AND languageCodeHL = :languageCodeHL
            ORDER BY weight DESC";
        $params = array(':title'=> $title, ':languageCodeHL'=> $languageCodeHL);
        try {
            $statement = $dbConnection->executeQuery($query, $params);
            $videoCode = $statement->fetch(PDO::FETCH_COLUMN);
            return $videoCode;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }

    }
}
