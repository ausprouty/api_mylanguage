<?php
namespace App\Controllers\Language;

use App\Models\Bible\BibleModel as BibleModel;
use App\Services\Database\DatabaseService;
use App\Models\Language\DbsLanguageModel as DbsLanguageModel;
use App\Models\Language\LanguageModel as LanguageModel;
use PDO as PDO;


class DbsLanguageController{

    protected $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

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
      
        $query = "SELECT dbs_languages.*, hl_languages.name,  hl_languages.ethnicName
                  FROM dbs_languages INNER JOIN hl_languages
                  ON dbs_languages.languageCodeHL = hl_languages.languageCodeHL
                  ORDER BY hl_languages.name";
        try {
            $results = $databaseService->executeQuery($query);
            $data = $results->fetchAll(PDO::FETCH_ASSOC);
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
