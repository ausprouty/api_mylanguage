<?php

namespace App\Models\Language;

use App\Services\Database\DatabaseService;
use App\Models\Video\VideoModel as VideoModel;
use PDO as PDO;
use stdClass as stdClass;

class CountryLanguageModel
{
    protected $databaseService;

    private $id;
    private $countryCode;
    private $languageCodeIso;
    private $languageCodeHL;
    private $languageNameEnglish;
   

    public function __construct(DatabaseService $databaseService){
        $this->databaseService = $databaseService;
        
        $this->countryCode= '';
        $this->langaugeCodeHL = '';
        $this->languageNameEnglish= '';
    }
    public function getLanguagesWithContentForCountry($countryCode){
       $query = "SELECT *
            FROM country_languages 
            WHERE countryCode = :countryCode
            AND languageCodeHL != :blank
            GROUP BY languageCodeHL
            ORDER BY languageNameEnglish";
        $params = array(':countryCode'=> $countryCode,
                    ':blank'=> '');
        try {
            $results =$this->databaseService->executeQuery($query, $params);
            $data = $results->fetchAll(PDO::FETCH_OBJ);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    public function addLanguageCodeJF($result){
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