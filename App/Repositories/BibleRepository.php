<?php
namespace App\Repositories;

use App\Services\Database\DatabaseService;
use PDO;

class BibleRepository {
    private $databaseService;

    public function __construct(DatabaseService $databaseService) {
        $this->databaseService = $databaseService;
    }
    public function addBibleBrainBible(){
        echo ("external id is $this->externalId<br>");
        $query = "SELECT bid  FROM bibles WHERE externalId = :externalId";
        $params = array(':externalId' => $this->externalId);
        $results = $this->databaseService->executeQuery($query, $params);
        $bid = $results->fetch(PDO::FETCH_COLUMN);
        if (!$bid){
            $query = "INSERT INTO bibles 
            (source, externalId, volumeName, volumeNameAlt, languageCodeHL, 
            languageName, languageEnglish,
            collectionCode,format, audio, text, video, dateVerified) 
            VALUES (:source, :externalId, :volumeName, :volumeNameAlt, 
            :languageCodeHL, :languageName, :languageEnglish,
            :collectionCode,:format,:audio,:text,:video,:dateVerified)";
            $params = array(
                ':source' => $this->source , 
                ':externalId' => $this->externalId , 
                ':volumeName' => $this->volumeName ,
                ':volumeNameAlt' => $this->volumeNameAlt, 
                ':languageCodeHL' => $this->languageCodeHL ,
                ':languageName' => $this->languageName,
                ':languageEnglish' => $this->languageEnglish,
                ':collectionCode' => $this->collectionCode ,':format' => $this->format ,
                ':audio' => $this->audio, ':text' => $this->text ,':video' => $this->video ,
                ':dateVerified' => $this->dateVerified);
            $this->databaseService->executeQuery($query, $params);
        }

    }

    private function executeAndFetchAll($query, $params) {
        try {
            $results =$this->databaseService->executeQuery($query, $params);
            $data = $results->fetchAll(PDO::FETCH_ASSOC);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function executeAndFetchColumn($query, $params) {
        try {
            $results =$this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_COLUMN);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function executeAndFetchOne($query, $params) {
        try {
            $results =$this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_ASSOC);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    public function findBestBibleByLanguageCodeHL($languageCodeHL){
        $query = "SELECT * FROM bibles 
            WHERE languageCodeHL = :code 
            ORDER BY weight DESC LIMIT 1";
        $params = array(':code'=>$languageCodeHL);
        return $this->executeAndFetchOne($query, $params);
    }
    public function findBestDbsBibleByLanguageCodeHL($code, $testament = 'C'){
        // 'C' for complete will be found AFTER 'NT' or 'OT'
        $query = "SELECT * FROM bibles 
            WHERE languageCodeHL = :code 
            AND (collectionCode = :complete OR collectionCode = :testament)
            AND weight = 9 
            ORDER BY collectionCode desc
            LIMIT 1";
        $params = array(
            ':code'=>$code,
            ':complete' => 'C',
            ':testament' => $testament
        );
        return $this->executeAndFetchOne($query, $params);
    }
    public function findBibleByBid($bid){
        $query = "SELECT * FROM bibles WHERE bid = :bid LIMIT 1";
        $params = array(':bid'=>$bid);
        return $this->executeAndFetchOne($query, $params);
    }
    public function findBibleByExternalId($externalId) {
        $query = "SELECT * FROM bibles 
            WHERE externalId = :externalId LIMIT 1";
        $params = array(':externalId'=>$externalId);
        return $this->executeAndFetchOne($query, $params);
    }

    public function getAllBiblesByLanguageCodeHL($languageCodeHL){
        $query = "SELECT * FROM bibles WHERE languageCodeHL = :code 
            ORDER BY volumeName";
        $params = array(':code'=>$languageCodeHL);
        return $this->executeAndFetchAll($query, $params);
       
    }
    
    public function getTextBiblesByLanguageCodeHL($languageCodeHL){

        $query = "SELECT * FROM bibles 
            WHERE languageCodeHL = :code 
            AND format NOT LIKE :audio 
            AND format NOT LIKE :video 
            AND format != :usx 
            AND format IS NOT NULL
            AND source != :dbt
            ORDER BY volumeName";
        $params = array(':code'=>$languageCodeHL, 
            ':audio' => 'audio%', 
            ':video' => 'video%',
            ':usx'=> 'text_usx',
            ':dbt' => 'dbt'
        );
        return $this->executeAndFetchAll($query, $parame);
    }

    public function hasOldTestament(string $languageCodeHL): bool{
        $query = "SELECT bid FROM  bibles WHERE languageCodeHL = :languageCodeHL AND
          (collectionCode = :OT OR collectionCode = :AL OR collectionCode = :C ) LIMIT 1";
        $params = array(
            ':languageCodeHL' => $languageCodeHL,
            ':OT' => 'OT',
            ':AL' => 'AL',
            ':C' => 'C'
        );
        $data =$this->executeAndFetchColumn($query, $params);
        return (bool) $data;
    }

    
    
    public function updateWeight($bid, $weight){
        $query = "UPDATE bibles 
            SET weight = :weight
            WHERE bid = :bid
            LIMIT 1";
        $params = array(':weight'=>$weight, 
            ':bid' => $bid, 
        );
        try {
           $this->databaseService->executeQuery($query, $params);
            return 'success';
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }


    }

   
}
