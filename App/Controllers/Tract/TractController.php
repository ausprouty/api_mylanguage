<?php
namespace App\Controllers\Tract;

use App\Models\Data\DatabaseConnectionModel as DatabaseConnectionModel;
use PDO as PDO;


class TractController extends Tract {

    static function findTractByLanguageCodes($languageCodeHL1,$languageCodeHL2){
        $dbConnection = new DatabaseConnectionModel();
        $query = "SELECT * FROM hl_bilingual_tracts
            WHERE languageCodeHL = :lang1 AND languageCodeHL2 = :lang2";
        $params = array(':lang1'=> $languageCodeHL1, ':lang2'=> $languageCodeHL2, );
        try {
            $statement = $dbConnection->executeQuery($query, $params);
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
        if (!$data){
            $query = "SELECT * FROM hl_bilingual_tracts
            WHERE languageCodeHL = :lang1 AND languageCodeHL2 = :lang2";
            $params = array(':lang1'=> $languageCodeHL2, ':lang2'=> $languageCodeHL1, );
            try {
                $statement = $dbConnection->executeQuery($query, $params);
                $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
                return null;
            }

        }
        if (strpos())
    }

}

