<?php

/*  see https://documenter.getpostman.com/view/12519377/Tz5p6dp7
*/
namespace App\Controllers\BiblePassage\BibleBrain;

use App\Models\Data\DatabaseConnectionModel as DatabaseConnectionModel;
use App\Models\Data\BibleBrainConnectionModel as BibleBrainConnectionModel;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BiblePassageModel as BiblePassageModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;

class BibleBrainPassageController extends BiblePassageModel {
    private $dbConnection;
    protected $bibleReferenceInfo;
    protected $bible;
    public $response;


    public function __construct( BibleReferenceInfoModel $bibleReferenceInfo, BibleModel $bible){
        $this->dbConnection = new DatabaseConnectionModel();
        $this->bibleReferenceInfo = $bibleReferenceInfo;
        $this->bible = $bible;
        $this->referenceLocalLanguage = '';
        $this->passageText = '';
        $this->setPassageUrl();
        $this->dateLastUsed = '';
        $this->dateChecked = '';
        $this->timesUsed = 0;
        $this->getExternal();
        $this->formatPassageText();
        $this->setReferenceLocalLanguage();
    }
 

    /* to get verses: https://4.dbt.io/api/bibles/filesets/:fileset_id/:book/:chapter?verse_start=5&verse_end=5&v=4
  */
    public function getExternal()
    {
        $url = 'https://4.dbt.io/api/bibles/filesets/' . $this->bible->getExternalId();
        $url .= '/'. $this->bibleReferenceInfo->getBookID() . '/'. $this->bibleReferenceInfo->getChapterStart();
        $url .= '?verse_start=' . $this->bibleReferenceInfo->getVerseStart() . '&verse_end=' .$this->bibleReferenceInfo->getVerseEnd();
        $passage =  new BibleBrainConnectionModel($url);
        $this->response = $passage->response;
    }
    function setPassageUrl(){
        // https://live.bible.is/bible/engesv/mat/1
        $this->passageUrl = 'https://live.bible.is/bible/'. $this->bible->getExternalId() . '/';
        $this->passageUrl  .= $this->bibleReferenceInfo->getbookID() . '/'. 
            $this->bibleReferenceInfo->getChapterStart();
    }
    function getBibleLanguageName(){
        return $this->bible->languageName;
    }
    function getBibleLanguageEnglish(){
        return $this->bible->languageEnglish;
    }
    public function formatPassageText()
    {   $text = null;
        $multiVerseLine = false;
        $startVerseNumber = null;
        if (!isset($this->response->data)){
            $this->passageText = NULL;
            return $this->passageText;
        }
        foreach ($this->response->data as $verse){
            if (!isset($verse->verse_text)){
                $text = NULL;
                break;
            }
            $verseNum = $verse->verse_start_alt;
            if ($multiVerseLine){
                $multiVerseLine = false;
                $verseNum = $startVerseNumber . '-' . $verse->verse_end_alt;
            }
            if ($verse->verse_text == '-'){
                $multiVerseLine = true;
                $startVerseNumber = $verse->verse_start_alt;
            }
            if ($verse->verse_text != '-') {
                $text .= '<p><sup class="versenum">' . $verseNum . '</sup> '. $verse->verse_text . '</p>';
            }

        }
         $this->passageText = $text;
        return $this->passageText;
    }

    public function setReferenceLocalLanguage(){
        $this->referenceLocalLanguage = $this->getBookNameLocalLanguage();
        $this->referenceLocalLanguage .= ' '. $this->bibleReferenceInfo->getChapterStart() . ':' .
        $this->bibleReferenceInfo->getVerseStart()  .'-' .$this->bibleReferenceInfo->getVerseEnd();
    }
    public function getReferenceLocalLanguage(){
        return $this->referenceLocalLanguage;
    }

    public function getBookNameLocalLanguage(){
        if (!isset($this->response->data)){
           return $this->bibleReferenceInfo->getBookName();
        }
        if (isset($this->response->data[0]->book_name_alt)){ 
            return $this->response->data[0]->book_name_alt;
        }
        else{
            return null;
        }
    }

}

