<?php

namespace App\Model\Language;

use App\Model\Data\DatabaseConnectionModel as DatabaseConnectionModel;
use PDO as PDO;

class DbsLanguageModel  {
    private $languageCodeHL;
    private $collectionCode; //  'C' for complete  'NT' for New Testament
    private $format;  
    
    public function __construct( $languageCodeHL = null, $collectionCode = null, $format = null){
        $this->languageCodeHL = $languageCodeHL;
        $this->collectionCode = $collectionCode;
        $this->format =  $format;
        $this->updateDatabase();
    }
    protected function updateDatabase(){
        $dbConnection = new DatabaseConnectionModel();
        $query = "SELECT languageCodeHL FROM dbs_languages 
            WHERE languageCodeHL = :code 
            LIMIT 1";
        $params = array(':code'=> $this->languageCodeHL);
        try {
            $statement = $dbConnection->executeQuery($query, $params);
            $data = $statement->fetchAll(PDO::FETCH_COLUMN);
            if ($data){
                $this->updateRecord();
            }
            else{
                $this->insertRecord();
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function updateRecord(){
        $dbConnection = new DatabaseConnectionModel();
        $query = "UPDATE  dbs_languages
            SET collectionCode = :collectionCode, format = :format
            WHERE languageCodeHL = :languageCodeHL 
            LIMIT 1";
        $params = array(
            ':collectionCode' => $this->collectionCode,
            ':format' => $this->format,
            ':languageCodeHL'=> $this->languageCodeHL);
        try {
            $statement = $dbConnection->executeQuery($query, $params);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function insertRecord(){
        $dbConnection = new DatabaseConnectionModel();
        $query = "INSERT INTO dbs_languages 
            (languageCodeHL, collectionCode, format)
            VALUES  (:languageCodeHL, :collectionCode, :format)";
        $params = array(
            ':languageCodeHL' => $this->languageCodeHL,
            ':collectionCode' => $this->collectionCode,
            ':format' => $this->format);
        try {
            $statement = $dbConnection->executeQuery($query, $params);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }

}