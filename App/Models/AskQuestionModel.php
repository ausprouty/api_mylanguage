<?php
namespace App\Model;

use App\Services\Database\DatabaseService;
use PDO as PDO;

class AskQuestionModel{
    protected $databaseService;

    private $id;
    private $langaugeCodeHL;
    private $name;
    private $ethnicName;
    private $url;
    private $contactPage;
    private $languageCodeTracts;
    private $promoText;
    private $promoImage;
    private $tagline;
    private $weight;

    public function __construct(protected $databaseService;){
        $this->databaseService = $databaseService;
        $this->dbConnection= '';
        $this->id= '';
        $this->langaugeCodeHL= '';
        $this->name= '';
        $this->ethnicName= '';
        $this->url= '';
        $this->contactPage= '';
        $this->languageCodeTracts= '';
        $this->promoText= '';
        $this->promoImage= '';
        $this->tagline= '';
        $this->weight= '';
    }
    public function setBestSiteByLanguageCodeHL($code){
        $dbService = new DatabaseService();
        $query = "SELECT * FROM ask_questions 
            WHERE languageCodeHL = :code 
            ORDER BY weight DESC LIMIT 1";
        $params = array(':code'=>$code);
        try {
            $statement = $dbService->executeQuery($query, $params);
            $data = $statement->fetch(PDO::FETCH_OBJ);
            $this->setValues($data);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    static function getBestSiteByLanguageCodeHL($code){
        $dbService = new DatabaseService();
        $query = "SELECT * FROM ask_questions 
            WHERE languageCodeHL = :code 
            ORDER BY weight DESC LIMIT 1";
        $params = array(':code'=>$code);
        try {
            $statement = $dbService->executeQuery($query, $params);
            $data = $statement->fetch(PDO::FETCH_OBJ);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function setValues($data){
        $this->id = $data->id;
        $this->langaugeCodeHL = $data->angaugeCodeHL;
        $this->name = $data->name;
        $this->ethnicName = $data->ethnicName;
        $this->url = $data->url;
        $this->contactPage = $data->contactPage;
        $this->languageCodeTracts = $data->languageCodeTracts;
        $this->promoText = $data->promoText;
        $this->promoImage = $data->promoImage;
        $this->tagline = $data->tagline;
        $this->weight = $data->weight;
    }
}