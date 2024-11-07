<?php

namespace App\Controllers\Language;

use App\Services\Database\DatabaseService
use App\Models\Video\VideoModel as VideoModel;
use PDO as PDO;
use stdClass as stdClass;


class HindiLanguageController{

    public function getLanguageOptions(){
        $result = $this->getLanguageData();
        $output = $this->addLanguageCodeJF($result);
        return $output;
    }

    public function getLanguageData(){
        $dbService = new DatabaseService();
        $query = "SELECT *
                  FROM hl_languages
                  WHERE isHindu  = 'Y'
                  ORDER BY name";
        try {
            $statement = $dbService->executeQuery($query);
            $result = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $result;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function addLanguageCodeJF($result){
        $data = [];
        foreach ($result as $language){
            $obj = new stdClass;
            $obj = $language;
            $obj['languageCodeJF'] = VideoModel::getLanguageCodeJF($language['languageCodeHL']);
            $data[] = $obj;
        }
        return $data;
    }
}
