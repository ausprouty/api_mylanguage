<?php

/* requires entry in form of 'Zephaniah 1:2-3'

 returns an object:
        'entry' => 'Zephaniah 1:2-3'
        'bookId' => 'Zeph',
        'chapterId' => 1,
        'verseStart' => 2,
        'verseEnd' => 3,
         collection_code' => 'OT' ,
 */
namespace App\Models\Bible;

use App\Services\Database\DatabaseService
use PDO as PDO;

class BibleReferenceInfoModel

{

    private $databaseService;
    private   $entry;
    private   $languageCodeHL;
    private   $bookName;
    private   $bookID;
    private   $uversionBookID;
    private   $bookNumber;
    private   $testament;
    private   $chapterStart;
    private   $verseStart;
    private   $chapterEnd;
    private   $verseEnd;


   public function __construct(){
        $this->dbConnection = new DatabaseService();
        $this->entry= ' ';
        $this->languageCodeHL= null;
        $this->bookName= ' ';
        $this->bookID= null;
        $this->uversionBookID = null;
        $this->bookNumber = null;
        $this->testament= null;
        $this->chapterStart= null;
        $this->verseStart= null;
        $this->chapterEnd= null;
        $this->verseEnd= null;
    }
    public function setFromEntry(string $entry, string $languageCodeHL = 'eng00'){
        $this->checkEntrySpacing($entry);
        $this->findBookName();
        $this->findBookID();
        $this->findBookNumber();
        $this->findUversionBookID();        
        $this->findTestament();
        $this->findChapterAndVerses();
    }
    public function getBookID(){
        return $this->bookID;
    }
    public function getBookName(){
        return $this->bookName;
    }
    public function getBookNumber(){
        return $this->bookNumber;
    }
    public function getChapterStart(){
        return $this->chapterStart;
    }
    public function getEntry(){
        return $this->entry;
    }
    public function getLanguageCodeHL(){
        return $this->languageCodeHL;
    }
    public function getTestament(){
        return $this->testament;
    }
    public function getUversionBookID(){
        return $this->uversionBookID;
    }
    public function getVerseStart(){
        return $this->verseStart;
    }
    public function getVerseRange(){
        return  $this->verseEnd -  $this->verseStart;
    }
    public function getVerseEnd(){
        return $this->verseEnd;
    }
   
    public function setFromDbtArray(array $dbtArray){
        print_r ($dbtArray);
        $entry =$this->checkEntrySpacing ($dbtArray['entry']);
        $this->entry= $entry;
        $this->languageCodeHL=null;
        $this->bookName= $this->findBookName();
        $this->bookID= $dbtArray['bookId'];
        $this->testament= $dbtArray['collection_code'];
        $this->chapterStart= $dbtArray['chapterId'];
        $this->verseStart= $dbtArray['verseStart'];
        $this->chapterEnd= null;
        $this->verseEnd= $dbtArray['verseEnd'];
    }

    private function checkEntrySpacing(string $entry){
        // chinese does not use a space before reference
        $entry = trim($entry);
        if (strpos($entry, ' ') === FALSE){
            $first_number= mb_strlen($entry);
            for ($i = 0; $i<=9; $i++){
                $pos = mb_strpos( $entry, $i);
                if ($pos){
                    if($pos < $first_number){
                        $first_number = $pos;
                    }
                }
            }
            $book= mb_substr($this->entry, 0, $first_number);
            $chapter = mb_substr($this->entry,$first_number);
            $entry= $book . ' '. $chapter;
        }
        $this->entry = $entry;
    }
    private function findBookName(){
        $parts = explode(' ', $this->entry);
        $book = $parts[0];
        if ($book == 1 || $book == 2 || $book == 3){
            $book .= ' '. $parts[1];
        }
        if ($book == 'Psalm'){
            $book = 'Psalms';
        }
        $this->bookName= $book;

    }
    private function findBookID(){
        $query = 'SELECT bookId FROM bible_book_names
            WHERE (languageCodeHL = :languageCodeHL OR languageCodeHL = :english)
            AND name = :book_lookup LIMIT 1';
        $params = array(
             ':languageCodeHL'=>$this->languageCodeHL, 
             ':english' => 'eng00', 
             ':book_lookup'=>$this->bookName );
        try {
            $statement = $this->dbConnection->executeQuery($query, $params);
            $this->bookID  = $statement->fetch(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function findBookNumber(){
        $query = 'SELECT bookNumber FROM bible_books
            WHERE bookId = :bookId
            LIMIT 1';
        $params = array(':bookId'=> $this->bookID);
        try {
            $statement = $this->dbConnection->executeQuery($query, $params);
            $bookNumber = $statement->fetch(PDO::FETCH_COLUMN);
            $this->bookNumber = $bookNumber;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function findUversionBookID(){
        $query = 'SELECT uversionBookID FROM bible_books
            WHERE bookId = :bookId
            LIMIT 1';
        $params = array(':bookId'=> $this->bookID);
        try {
            $statement = $this->dbConnection->executeQuery($query, $params);
            $uversionBookID = $statement->fetch(PDO::FETCH_COLUMN);
            $this->uversionBookID = $uversionBookID;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function findTestament(){
        $query = 'SELECT testament FROM bible_books
            WHERE bookId = :bookId  LIMIT 1';
        $params = array(':bookId'=>$this->bookID );
        try {
            $statement = $this->dbConnection->executeQuery($query, $params);
            $testament = $statement->fetch(PDO::FETCH_COLUMN);
            $this->testament = $testament;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function findChapterAndVerses(){
        $pass = str_replace($this->bookName, '', $this->entry);
        $pass = str_replace(' ' , '', $pass);
        $pass = str_replace('፡' , ':', $pass); // from Amharic
        $i = strpos($pass, ':');
        if ($i == FALSE){
            // this is the whole chapter
            $this->chapterStart = trim($pass);
            $this->verseStart = 1;
            $this->verseEnd = 999;
        }
        else{
            $this->chapterStart = substr($pass, 0, $i);
            $verses = substr($pass, $i+1);
            $i = strpos ($verses, '-');
            if ($i !== FALSE){
                $this->verseStart = substr($verses, 0, $i);
                $this->verseEnd = substr($verses, $i+1);
            }
            else{
                $this->verseStart = $verses;
                $this->verseEnd = $verses;
            }
        }
    }
    public function getPublic(){
            $export= new stdClass();
            $export->entry = $this->entry;
            $export->bookName = $this->bookName;
            $export->bookID = $this->bookID;
            $export->uversionBookID = $this->uversionBookID;
            $export->bookNumber =  $this->bookNumber;
            $export->testament = $this->testament;
            $export->chapterStart = $this->chapterStart;
            $export->verseStart = $this->verseStart;
            $export->chapterEnd = $this->chapterEnd;
            $export->verseEnd = $this->verseEnd;
            return  $export;
    }

    public function setPublic($import)
    {
        $this->entry = $import->entry;
        $this->bookName = $import->bookName;
        $this->bookID = $import->bookID;
        $this->uversionBookID = $import->uversionBookID;
        $this->bookNumber =  $import->bookNumber;
        $this->testament = $import->testament;
        $this->chapterStart = $import->chapterStart;
        $this->verseStart = $import->verseStart;
        $this->chapterEnd = $import->chapterEnd;
        $this->verseEnd = $import->verseEnd;
    }

}