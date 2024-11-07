<?php

namespace App\Controller\BiblePassage;


use App\Controller\BiblePassage\BibleYouVersionPassageController as BibleYouVersionPassageController;
use App\Controller\BiblePassage\BibleWordPassageController as BibleWordPassageController;
use App\Controller\BiblePassage\BibleBrain\BibleBrainTextPlainController as BibleBrainTextPlainController;
use App\Controller\BiblePassage\BibleGateway\BibleGatewayPassageController as  BibleGatewayPassageController;
use App\Model\Bible\BibleModel as BibleModel;
use App\Model\Bible\BiblePassageModel as BiblePassageModel;
use App\Model\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Model\Data\DatabaseConnectionModel as DatabaseConnectionModel;
use App\Model\Language\LanguageModel as LanguageModel;

class PassageSelectController extends BiblePassageModel
{

    //private $dbConnection;
    protected $bibleReferenceInfo;
    private $bible;
    private $passageId;// used to see if data is stored
    public  $passageText;
    public  $passageUrl;
    public  $referenceLocalLanguage;

    public function __construct( BibleReferenceInfoModel $bibleReferenceInfo, BibleModel $bible){
        //$this->dbConnection = new DatabaseConnectionModel();
            $this->bibleReferenceInfo=$bibleReferenceInfo;
            $this->bible = $bible;
            $this->passageText= null;
            $this->passageUrl= null;
           $this->checkDatabase();
      
    }
    public function getBible(){
        return $this->bible;
    }
    public function getBibleDirection(){
        return $this->bible->getDirection();
    }
    public function getBibleBid(){
        return $this->bible->getBid();
    }
    public function getBibleReferenceInfo(){
        return $this->bibleReferenceInfo;
    }
    private  function checkDatabase(){
        $this->passageId = BiblePassageModel::createBiblePassageId($this->bible->getBid(),  $this->bibleReferenceInfo);
        $passage = new BiblePassageModel();
        $passage->findStoredById($this->passageId);
        if ($passage->getReferenceLocalLanguage()) {
            $this->passageText= $passage->getPassageText();
            $this->passageUrl = $passage->getPassageUrl();
            $this->referenceLocalLanguage = $passage->getReferenceLocalLanguage();
        }
        else{
            $this->getExternal();
        }
        $this->wrapTextDir();
    }
    private function getExternal(){
        switch($this->bible->getSource()){
            case 'bible_brain':
                $passage = new BibleBrainTextPlainController($this->bibleReferenceInfo, $this->bible);
                break;
            case 'bible_gateway':
                $passage = new BibleGatewayPassageController($this->bibleReferenceInfo, $this->bible);
                break;
            case 'youversion':
                $passage = new BibleYouVersionPassageController($this->bibleReferenceInfo, $this->bible);
                break;    
            case 'word':
                $passage = new BibleWordPassageController($this->bibleReferenceInfo, $this->bible);
                break;
            default:
                $this->passageText = '';
                $this->passageUrl = '';
                $this->referenceLocalLanguage = ' ';
                return;
            break;
        }
        $this->passageText = $passage->getPassageText();
        $this->passageUrl = $passage->getPassageUrl();
        $this->referenceLocalLanguage = $passage->getReferenceLocalLanguage();
        parent::savePassageRecord($this->passageId, $this->referenceLocalLanguage,  $this->passageText, $this->passageUrl); 
    }
    private function wrapTextDir(){
        if ($this->passageText == NULL){
            return;
        }
        if ($this->bible->direction == 'rtl'){
            $dir = 'rtl';
        }
        elseif ($this->bible->direction == 'ltr'){
            $dir = 'ltr';
        }
        else{
            $dir = $this->updateDirection();
        }
        $text = '<div dir="' . $dir . '">' ;
        $text .=  $this->passageText;
        $text .=  '</div>';
        $this->passageText = $text;
    }
    private function updateDirection(){
        $languageCodeHL = $this->bible->getLanguageCodeHL();
        $language = new LanguageModel();
        $language->findOneByLanguageCodeHL( $languageCodeHL);
        $direction = $language->getDirection();
        $dir = 'ltr';
        if ($direction == 'rtl'){
            $dir = 'rtl';
        }
        $dbConnection = new DatabaseConnectionModel();
        $query = "UPDATE bibles
            SET direction = :dir
            WHERE languageCodeHL = :languageCodeHL";
        $params = array(
            ':languageCodeHL'=>  $languageCodeHL,
            ':dir'=> $dir
        );
        $statement = $dbConnection->executeQuery($query, $params);
        return $dir;
    }

}