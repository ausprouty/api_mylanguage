<?php
namespace App\Controllers\Tract;

use App\Services\Database\DatabaseService;
use PDO as PDO;


class TractController extends Tract {

    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function findTractByLanguageCodes($languageCodeHL1,$languageCodeHL2){

        $query = "SELECT * FROM hl_bilingual_tracts
            WHERE languageCodeHL = :lang1 AND languageCodeHL2 = :lang2";
        $params = array(':lang1'=> $languageCodeHL1, ':lang2'=> $languageCodeHL2, );
        try {
            $results = $databaseService->executeQuery($query, $params);
            $data = $results->fetchAll(PDO::FETCH_ASSOC);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
        if (!$data){
            $query = "SELECT * FROM hl_bilingual_tracts
            WHERE languageCodeHL = :lang1 AND languageCodeHL2 = :lang2";
            $params = array(':lang1'=> $languageCodeHL2, ':lang2'=> $languageCodeHL1, );
            try {
                $results = $databaseService->executeQuery($query, $params);
                $data = $results->fetchAll(PDO::FETCH_ASSOC);
            } catch (Exception $e) {
                echo "Error: " . $e->getMessage();
                return null;
            }

        }
    }

}

