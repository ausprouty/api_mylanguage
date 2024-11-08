<?php

namespace App\Controllers\BiblePassage;


use App\Controllers\BiblePassage\BibleYouVersionPassageController as BibleYouVersionPassageController;
use App\Controllers\BiblePassage\BibleWordPassageController as BibleWordPassageController;
use App\Controllers\BiblePassage\BibleBrain\BibleBrainTextPlainController as BibleBrainTextPlainController;
use App\Controllers\BiblePassage\BibleGateway\BibleGatewayPassageController as  BibleGatewayPassageController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BiblePassageModel as BiblePassageModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Services\Database\DatabaseService;
use App\Models\Language\LanguageModel as LanguageModel;
use App\Repositories\LanguageRepository as LanguageRepository;

class PassageSelectController extends BiblePassageModel
{
    private $languageRepository;
    protected $databaseService;
    protected $bibleReferenceInfo;
    private $bible;
    private $passageId;// used to see if data is stored
    public  $passageText;
    public  $passageUrl;
    public  $referenceLocalLanguage;

    public function __construct(
        DatabaseService $databaseService,  
        BibleReferenceInfoModel $bibleReferenceInfo, 
        BibleModel $bible,
        LanguageRepository $languageRepository
        ){
            $this->databaseService = $databaseService;
            $this->bibleReferenceInfo = $bibleReferenceInfo;
            $this->languageRepository = $languageRepository;
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
        $language = new LanguageModel($lthis->anguageRepository);
        $language->findOneByLanguageCodeHL( $languageCodeHL);
        $direction = $language->getDirection();
        $dir = 'ltr';
        if ($direction == 'rtl'){
            $dir = 'rtl';
        }
        $query = "UPDATE bibles
            SET direction = :dir
            WHERE languageCodeHL = :languageCodeHL";
        $params = array(
            ':languageCodeHL'=>  $languageCodeHL,
            ':dir'=> $dir
        );
        $results = $this->databaseService->executeQuery($query, $params);
        return $dir;
    }

}