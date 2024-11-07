<?php
namespace App\Controllers\BibleStudy;

use App\Services\Database\DatabaseService
use App\Models\Language\TranslationModel as TranslationModel;
use PDO as PDO;
use stdClass as stdClass;

class LifeStudyController{
    private $data;

    public function __construct(){
        $dbService = new DatabaseService();
        $query = "SELECT * FROM life_principle_references
        ORDER BY lesson";
        try {
            $statement = $dbService->executeQuery($query);
            $this->data = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    public function formatWithEnglishTitle(){
        $formated = [];
        foreach ($this->data as $lesson){
            $title = $lesson ['lesson'] . '. ' . $lesson['description']  . ' (' . $lesson['reference'] . ')';
            $obj =  new stdClass();
            $obj->title = $title;
            $obj->lesson = $lesson['lesson'];
            $obj->testament = $lesson['testament'];
            $formatted[] = $obj;
        }
        return $formatted;
    }

    public function formatWithEthnicTitle($languageCodeHL){
        $formated = [];
        $translation = new TranslationModel($languageCodeHL, 'life');
        foreach ($this->data as $lesson){
            $translated = $translation->translateText ($lesson['description']);
            $title = $lesson ['lesson'] . '. ' . $translated ;
            $obj =  new stdClass();
            $obj->title = $title;
            $obj->lesson = $lesson['lesson'];
            $obj->testament = $lesson['testament'];
            $formatted[] = $obj;
        }
        return $formatted;
    }
    static function getTitle($lesson, $languageCodeHL){
        $dbService = new DatabaseService();
        if ($languageCodeHL != 'eng00'){
            $translation = new TranslationModel($languageCodeHL, 'life');
        }
        $query = "SELECT description FROM life_principle_references
        WHERE lesson = :lesson";
        $params = array(':lesson'=> $lesson);
        try {
            $statement = $dbService->executeQuery($query, $params);
            $title = $statement->fetch(PDO::FETCH_COLUMN);
            if ($languageCodeHL != 'eng00'){
                $title = $translation->translateText ($title);
            }
            return  $title;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}