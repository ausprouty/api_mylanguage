<?php
namespace App\Controllers\BibleStudy;

use App\Services\Database\DatabaseService;
use App\Models\Language\TranslationModel as TranslationModel;
use PDO as PDO;
use stdClass as stdClass;

class LeadershipStudyController{
    protected $databaseService;
    private $data;

    public function __construct(DatabaseService $databaseService){
        $this->databaseService = $databaseService;
        $query = "SELECT * FROM leadership_references
        ORDER BY lesson";
        try {
            $results = $databaseService->executeQuery($query);
            $this->data = $results->fetchAll(PDO::FETCH_ASSOC);
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
        $translation = new TranslationModel($languageCodeHL, 'leadership');
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
        $databaseService = new DatabaseService();
        if ($languageCodeHL != 'eng00'){
            $translation = new TranslationModel($languageCodeHL, 'leadership');
        }
        $query = "SELECT lesson, description FROM leadership_references
        WHERE lesson = :lesson";
        $params = array(':lesson'=> $lesson);
        try {
            $results = $databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_OBJ);
            $title = $data->description;
            if ($languageCodeHL != 'eng00'){
                $title = $translation->translateText ($title);
            }
            return $data->lesson .'. '. $title;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}