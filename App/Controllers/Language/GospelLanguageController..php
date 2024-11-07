<?php
namespace App\Controllers\Language;

use App\Services\Database\DatabaseService
use PDO as PDO;


class GospelLanguageController{
    
    public function getBilingualOptions(){
        $dbService = new DatabaseService();
        $query = "SELECT languageCodeHL1, languageCodeHL2, name, webpage
                  FROM hl_bilingual_tracts 
                  WHERE valid != 0
                  ORDER BY name";
        try {
            $statement = $dbService->executeQuery($query);
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}
