<?php
namespace App\Controllers\Language;

use App\Model\Bible\BibleModel as BibleModel;
use App\Model\Data\DatabaseConnectionModel as DatabaseConnectionModel;
use App\Model\Language\DbsLanguageModel as DbsLanguageModel;
use App\Model\Language\LanguageModel as LanguageModel;
use PDO as PDO;


class DbsLanguageController{

    public function updateDatabase(){
        $directory = ROOT_TRANSLATIONS . 'languages/';
        $scanned_directory = array_diff(scandir($directory), array('..', '.'));
        foreach ($scanned_directory as $languageCodeHL){
            $bible = BibleModel::getBestBibleByLanguageCodeHL($languageCodeHL);
            if (!$bible) {
                continue;
            }
            if ($bible->weight != 9){
                continue;
            }
            if ($bible->source == 'youversion'){
                $format = 'link';
            }
            else{
                $format = 'text';
            }
            $collectionCode = $bible->collectionCode;
            $dbs = new  DbsLanguageModel($languageCodeHL, $collectionCode, $format);
        }
    }
    public function getOptions(){
        $dbConnection = new DatabaseConnectionModel();
        $query = "SELECT dbs_languages.*, hl_languages.name,  hl_languages.ethnicName
                  FROM dbs_languages INNER JOIN hl_languages
                  ON dbs_languages.languageCodeHL = hl_languages.languageCodeHL
                  ORDER BY hl_languages.name";
        try {
            $statement = $dbConnection->executeQuery($query);
            $data = $statement->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    static function bilingualDbsPublicFilename($languageCodeHL1, $languageCodeHL2, $lesson, $type= 'DBS' ){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $lang2 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL2);
        $title =  $type .'#'. $lesson .'('. $lang1 . '-' . $lang2 .')';
        return trim($title);
    }
    // the following are depreciated.
    static function bilingualDbsPdfFilename($languageCodeHL1, $languageCodeHL2, $lesson, $type= 'DBS' ){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $lang2 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL2);
        $title =  $type . $lesson .'('. $lang1 . '-' . $lang2 .').pdf';
        return trim($title);
    }
    static function bilingualDbsViewFilename($languageCodeHL1, $languageCodeHL2, $lesson, $type= 'DBS' ){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $lang2 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL2);
        $title =  $type . $lesson .'('. $lang1 . '-' . $lang2 .').html';
        return trim($title);
    }
    static function monolingualDbsPublicFilename($lesson, $languageCodeHL1, $type= 'DBS' ){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $title =  $type .'#'. $lesson .'('. $lang1  .')';
        return trim($title);
    }
    // the following are depreciated.
    static function monolingualDbsPdfFilename($languageCodeHL1,  $lesson, $type= 'DBS' ){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $title =  $type . $lesson .'('. $lang1  .').pdf';
        return trim($title);
    }
    static function monolingualDbsViewFilename($lesson, $languageCodeHL1, $type= 'DBS' ){
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        $title =  $type . $lesson .'('. $lang1 .').html';
        return trim($title);
    }
}
