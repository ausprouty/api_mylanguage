<?php
namespace App\Controllers\BibleStudy;

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Data\DatabaseConnectionModel as DatabaseConnectionModel;
use App\Models\Language\TranslationModel as TranslationModel;
use PDO as PDO;
use StdClass as StdClass;

class DbsStudyController{
    private $data;

    public function __construct(){
        $dbConnection = new DatabaseConnectionModel();
        $query = "SELECT * FROM dbs_references
        ORDER BY lesson";
        try {
            $statement = $dbConnection->executeQuery($query);
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
        $otAvailable = BibleModel::oldTestamentAvailable($languageCodeHL);
        $translation = new TranslationModel($languageCodeHL, 'dbs');
        foreach ($this->data as $lesson){
            if ($lesson['testament'] == 'NT' || ($lesson['testament'] == 'OT' && $otAvailable)){
                $translated = $translation->translateText ($lesson['description']);
                $title = $lesson ['lesson'] . '. ' . $translated ;
                $obj =  new stdClass();
                $obj->title = $title;
                $obj->lesson = $lesson['lesson'];
                $obj->testament = $lesson['testament'];
                $formatted[] = $obj;
            }
        }
        return $formatted;
    }
    static function getTitle($lesson, $languageCodeHL){
        $dbConnection = new DatabaseConnectionModel();
        if ($languageCodeHL != 'eng00'){
            $translation = new TranslationModel($languageCodeHL, 'dbs');
        }
        $query = "SELECT description FROM dbs_references
        WHERE lesson = :lesson";
        $params = array(':lesson'=> $lesson);
        try {
            $statement = $dbConnection->executeQuery($query, $params);
            $title = $statement->fetch(PDO::FETCH_COLUMN);
            if ($languageCodeHL != 'eng00'){
                $title = $translation->translateText($title);
            }
            return $lesson . '. '. $title;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}