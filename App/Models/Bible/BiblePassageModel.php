<?php
namespace App\Models\Bible;

use App\Services\Database\DatabaseService;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use PDO as PDO;

class BiblePassageModel
{
    private    $databaseService;

    public     $bpid;
    protected  $referenceLocalLanguage;
    protected  $passageText;
    protected  $passageUrl;
    protected  $dateLastUsed;
    protected  $dateChecked;
    protected  $timesUsed;

    public function __construct(DatabaseService $databaseService){
        $this->databaseService = $databaseService;
        
        $this->bpid = '';
        $this->referenceLocalLanguage= '';
        $this->passageText = '';
        $this->passageUrl='';
        $this->dateLastUsed = '';
        $this->dateChecked = '';
        $this->timesUsed= 0;
    }
    public function getPassageText(){
        return $this->passageText;
    }
    public function getPassageUrl(){
        return $this->passageUrl;
    }
    public function getReferenceLocalLanguage(){
        return $this->referenceLocalLanguage;
    }
    public  function createBiblePassageId(string $bid, BibleReferenceInfoModel $passage){
        // 1026-Luke-10-1-42
            $bpid=$bid .'-' .
            $passage->getBookID() . '-' .
            $passage->getChapterStart() . '-'.
            $passage->getVerseStart() . '-' .
            $passage->getVerseEnd();
        return $bpid;
    }
    public function findStoredById($bpid){
        $query = "SELECT * FROM bible_passages WHERE bpid = :bpid LIMIT 1";
        $params = array('bpid'=>$bpid);
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_OBJ);
            if ($data){
                $this->bpid= $data->bpid;
                $this->referenceLocalLanguage = $data->referenceLocalLanguage;
                $this->passageText = $data->passageText;
                $this->passageUrl = $data->passageUrl;
                $this->dateLastUsed = $data->dateLastUsed;
                $this->dateChecked = $data->dateChecked;
                $this->timesUsed = $data->timesUsed;
                $this->updatePassageUse();
            }
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    
   

   
    protected function insertPassageRecord($bpid, $referenceLocalLanguage,  $passageText, $passageUrl){
        if ($passageText) {
            $dateLastUsed = date("Y-m-d");
            $query = "INSERT INTO bible_passages (bpid, referenceLocalLanguage,  passageText, passageUrl, dateLastUsed, dateChecked, timesUsed)
            VALUES (:bpid,:referenceLocalLanguage, :passageText, :passageUrl, :dateLastUsed, :dateChecked, :timesUsed)";
            $params = array(
                ':bpid' => $bpid,
                'referenceLocalLanguage' => $referenceLocalLanguage,
                ':passageText' => $passageText,
                ':passageUrl' => $passageUrl,
                ':dateLastUsed' => $dateLastUsed,
                ':dateChecked' => null,
                ':timesUsed' => 1
            );
            $this->databaseService->executeQuery($query, $params);
        }
    }
    protected function savePassageRecord($bpid, $referenceLocalLanguage,  $passageText, $passageUrl){
        $query = 'SELECT bpid FROM bible_passages WHERE bpid = :bpid LIMIT 1';
        $params = array(':bpid'=> $bpid);
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            $data = $results->fetch(PDO::FETCH_OBJ);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
        if ($data){
            $query = "UPDATE bible_passages
                SET  referenceLocalLanguage = :referenceLocalLanguage,
                passageText = :passageText,
                passageUrl = :passageUrl
                WHERE bpid = :bpid LIMIT 1";
            $params = array(
                ':referenceLocalLanguage' => $referenceLocalLanguage,
                ':passageText'=> $passageText,
                ':passageUrl'=> $passageUrl,
                ':bpid' => $this->bpid
            );
            $this->databaseService->executeQuery($query, $params);
        }
        else{
           $this->insertPassageRecord($bpid, $referenceLocalLanguage,  $passageText, $passageUrl);
        }
    }
    protected function updateDateChecked(){
        $query = "UPDATE bible_passages
            SET  dateChecked = :today
            WHERE bpid = :bpid LIMIT 1";
        $params = array(
            ':today' => date("Y-m-d"),
            ':bpid' => $this->bpid
        );
        $this->databaseService->executeQuery($query, $params);
    }
    protected function updatePassageUrl(){
        $query = "UPDATE bible_passages
            SET  passageUrl = :passageUrl
            WHERE bpid = :bpid LIMIT 1";
        $params = array(
            ':passageUrl' => $this->passageUrl,
            ':bpid' => $this->bpid
        );
        $this->databaseService->executeQuery($query, $params);
    }

    private function updatePassageUse(){
        $this->dateLastUsed = date("Y-m-d");
        $this->timesUsed = $this->timesUsed + 1;
        $query = "UPDATE bible_passages
            SET dateLastUsed = :dateLastUsed, timesUsed = :timesUsed
            WHERE bpid = :bpid LIMIT 1";
        $params = array(
            ':dateLastUsed' => $this->dateLastUsed,
            ':timesUsed' =>  $this->timesUsed,
            ':bpid' => $this->bpid
        );
        $this->databaseService->executeQuery($query, $params);
    }
    protected function updatereferenceLocalLanguage(){ 
        echo ("In updatereferenceLocalLanguage with value of $this->referenceLocalLanguage and bpid of $this->bpid");
        $query = "UPDATE bible_passages
            SET referenceLocalLanguage = :referenceLocalLanguage
            WHERE bpid = :bpid LIMIT 1";
        $params = array(
            ':referenceLocalLanguage' => $this->referenceLocalLanguage,
            ':bpid' => $this->bpid
        );
        $this->databaseService->executeQuery($query, $params);
    }
    protected function updatepassageText(){
        $query = "UPDATE bible_passages
            SET  text = :text
            WHERE bpid = :bpid LIMIT 1";
        $params = array(
            ':text' => $this->passageText,
            ':bpid' => $this->bpid
        );
        $this->databaseService->executeQuery($query, $params);
    }
}