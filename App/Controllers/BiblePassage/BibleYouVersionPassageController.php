<?php

/*  see https://documenter.getpostman.com/view/12519377/Tz5p6dp7
*/
namespace App\Controllers\BiblePassage;

use App\Model\Bible\BibleModel as BibleModel;
use App\Model\Bible\BiblePassageModel as BiblePassageModel;
use App\Model\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Model\Data\DatabaseConnectionModel as DatabaseConnectionModel;
use App\Model\Data\WebsiteConnectionModel as WebsiteConnectionModel;
use PDO as PDO;

class BibleYouVersionPassageController extends BiblePassageModel {

    private $bibleReferenceInfo;
    private $bible;
    private $bookName;
    public  $response;
    private $chapterAndVerse;
    private $retrieveBookName;

    public function __construct( BibleReferenceInfoModel $bibleReferenceInfo, BibleModel $bible){
      
        $this->bibleReferenceInfo = $bibleReferenceInfo;
        $this->bible = $bible;
        $this->passageText = '';
        $this->retrieveBookName = '';
        $this->setPassageUrl();
        $this->dateLastUsed = '';
        $this->dateChecked = '';
        $this->timesUsed = 0;
        $this->setChapterAndVerse();
        $this->setReferenceLocalLanguage();
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
    private function setPassageUrl(){
        $uversionBibleBookID =  $this->bibleReferenceInfo->getUversionBookID(); //GEN
        $bibleBookAndChapter =   $uversionBibleBookID . '.' . $this->bibleReferenceInfo->getChapterStart() . '.'; // GEN.1.
        $bibleBookAndChapter .=   $this->bibleReferenceInfo->getVerseStart() . '-'. $this->bibleReferenceInfo->getVerseEnd() ; // GEN.1
        $chapter = str_replace('%', $bibleBookAndChapter , $this->bible->getExternalId()); // 11/%.NIV   => /111/GEN.1.NIV
        $this->passageUrl = 'https://www.bible.com/bible/'. $chapter;
    }
    private function setChapterAndVerse(){
        $this->chapterAndVerse =  $this->bibleReferenceInfo->getChapterStart() . ':'; 
        $this->chapterAndVerse .=   $this->bibleReferenceInfo->getVerseStart() . '-'. $this->bibleReferenceInfo->getVerseEnd() ;
    }
    private function setReferenceLocalLanguage(){
        // <meta content="ԾՆՈՒՆԴ 1:1-28 ՍԿԶԲՈՒՄ Աստված ստեղծեց երկինքն ու երկիրը։
        $this->retrieveBookName();
        $this->referenceLocalLanguage = $this->bookName . ' '. $this->chapterAndVerse;
    }
    private function retrieveBookName(){
        $dbConnection = new DatabaseConnectionModel();
        $query = "SELECT name FROM bible_book_names
            WHERE languageCodeHL = :languageCodeHL
            AND bookID = :bookID 
            LIMIT 1";
        $params = array(
            ':languageCodeHL'=> $this->bibleReferenceInfo->getLanguageCodeHL(),
            ':bookID' => $this->bibleReferenceInfo->getbookID(),
        );
        $statement = $dbConnection->executeQuery($query, $params);
        $this->bookName = $statement->fetch(PDO::FETCH_COLUMN);
        if (!$this->bookName){
            $this->retrieveExternalBookName();
            if ($this->bookName){
                $this->saveBookName();
            }
        }
        
    }
    private function retrieveExternalBookName(){
        $webpage = $this->getExternal();
        $posEnd = strpos($webpage, $this->chapterAndVerse);
        if ($posEnd){
            $short = substr($webpage, 0, $posEnd);
            $posBegin = strrpos($short , '"') + 1;
            $this->bookName = trim (substr($short, $posBegin));
        }
        else{
            $find = 'class="book-chapter-text">';
            $posBegin = strpos($webpage, $find);
            if ($posBegin){
                $posEnd = strpos($webpage, '</h1>', $posBegin);
                $posBegin = $posBegin + length($find);
                $length = $posEnd-$posBegin;
                $this->bookName = trim (substr($webpage, $posBegin, $length));


            }
            else{
                $this->bookName = null;
            }

        }
       
    }
    private function saveBookName(){
        $dbConnection = new DatabaseConnectionModel();
        $query = "INSERT INTO bible_book_names
        (bookId, languageCodeHL, name)
        VALUES (:bookId, :languageCodeHL, :name)";
        $params = array(
            ':bookId' => $this->bibleReferenceInfo->getBookId(), 
            ':languageCodeHL'=> $this->bible->getLanguageCodeHL(),
            ':name' => $this->bookName,
        );
        $statement = $dbConnection->executeQuery($query, $params);
    }
    /* to get verses: https://www.bible.com/bible/111/GEN.1.7-14.NIV
    https://www.bible.com/bible/37/GEN.1.7-14.CEB
  */
    private function getExternal()  {
        $uversionBibleBookID =  $this->bibleReferenceInfo->getUversionBookID(); //GEN
        $bibleBookAndChapter =   $uversionBibleBookID . '.' . $this->bibleReferenceInfo->getChapterStart() . '.'; // GEN.1.
        $bibleBookAndChapter .=   $this->bibleReferenceInfo->getVerseStart() . '-'. 
                                  $this->bibleReferenceInfo->getVerseEnd() ; // GEN.1
        $chapter = str_replace('%', $bibleBookAndChapter , $this->bible->getExternalId()); // 11/%.NIV   => /111/GEN.1.NIV
        $chapter = str_replace(' ', '%20', $chapter); // some uversion Bibles have a space in their name
        $url = 'https://www.bible.com/bible/'. $chapter;
        $webpage = new WebsiteConnectionModel($url);
        return $webpage->response;
    }
    private function formatExternalText($webpage){
        //todo: we are not yet using this.  We are using reference instead
        $begin = '<div class="ChapterContent_label';
        $end = '<div class="ChapterContent_version-copyright';
    }
}