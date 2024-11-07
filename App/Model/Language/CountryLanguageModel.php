<?php

namespace App\Model\Language;

use App\Model\Data\DatabaseConnectionModel as DatabaseConnectionModel;
use  App\Model\Video\VideoModel as VideoModel;
use PDO as PDO;
use stdClass as stdClass;

class CountryLanguageModel
{
    private $id;
    private $countryCode;
    private $languageCodeIso;
    private $languageCodeHL;
    private $languageNameEnglish;
   

    public function __construct(){
        $this->countryCode= '';
        $this->langaugeCodeHL = '';
        $this->languageNameEnglish= '';
    }
    static function getLanguagesWithContentForCountry($countryCode){
       $dbConnection = new DatabaseConnectionModel();
       $query = "SELECT *
            FROM country_languages 
            WHERE countryCode = :countryCode
            AND languageCodeHL != :blank
            GROUP BY languageCodeHL
            ORDER BY languageNameEnglish";
        $params = array(':countryCode'=> $countryCode,
                    ':blank'=> '');
        try {
            $statement = $dbConnection->executeQuery($query, $params);
            $data = $statement->fetchAll(PDO::FETCH_OBJ);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    static function addLanguageCodeJF($result){
        $data = [];
        foreach ($result as $language){
            $obj = new stdClass;
            $obj = $language;
            $obj->languageCodeJF = VideoModel::getLanguageCodeJF($language->languageCodeHL);
            $data[] = $obj;
        }
        return $data;
    }
}