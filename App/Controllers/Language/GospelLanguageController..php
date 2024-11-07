<?php
namespace App\Controllers\Language;

use App\Model\Data\DatabaseConnectionModel as DatabaseConnectionModel;
use PDO as PDO;


class GospelLanguageController{
    
    public function getBilingualOptions(){
        $dbConnection = new DatabaseConnectionModel();
        $query = "SELECT languageCodeHL1, languageCodeHL2, name, webpage
                  FROM hl_bilingual_tracts 
                  WHERE valid != 0
                  ORDER BY name";
        try {
            $statement = $dbConnection->executeQuery($query);
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}
