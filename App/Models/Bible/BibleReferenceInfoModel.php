<?php

namespace App\Models\Bible;

use App\Repositories\BibleReferenceInfoRepository;
use App\Services\Database\DatabaseService;

class BibleReferenceInfoModel
{

    private $repository;

    private $entry;
    private $languageCodeHL;
    private $languageCodeIso;
    private $bookName;
    private $bookID;
    private $uversionBookID;
    private $bookNumber;
    private $testament;
    private $chapterStart;
    private $verseStart;
    private $chapterEnd;
    private $verseEnd;

    public function __construct(BibleReferenceInfoRepository $repository)
    {

        $this->repository = $repository;

        $this->entry = ' ';
        $this->languageCodeHL = null;
        $this->languageCodeIso = null;
        $this->bookName = ' ';
        $this->bookID = null;
        $this->uversionBookID = null;
        $this->bookNumber = null;
        $this->testament = null;
        $this->chapterStart = null;
        $this->verseStart = null;
        $this->chapterEnd = null;
        $this->verseEnd = null;
    }




    public function setFromDbtArray(array $dbtArray)
    {
        $entry = $this->checkEntrySpacing($dbtArray['entry']);
        $this->entry = $entry;
        $this->languageCodeHL = null;
        $this->bookName = $this->setBookName();
        $this->bookID = $dbtArray['bookId'];
        $this->testament = $dbtArray['collection_code'];
        $this->chapterStart = $dbtArray['chapterId'];
        $this->verseStart = $dbtArray['verseStart'];
        $this->chapterEnd = null;
        $this->verseEnd = $dbtArray['verseEnd'];
    }

    public function setFromEntry(string $entry, string $languageCodeHL = 'eng00')
    {
        $this->entry = $this->checkEntrySpacing($entry);
        $this->languageCodeHL = $languageCodeHL;

        // Fetch all book details in one call
        $bookDetails = $this->repository->getBookDetails($this->languageCodeHL, $this->entry);

        if ($bookDetails) {
            $this->bookID = $bookDetails['bookId'];
            $this->bookName = $bookDetails['bookName'];
            $this->bookNumber = $bookDetails['bookNumber'];
            $this->testament = $bookDetails['testament'];
            $this->uversionBookID = $bookDetails['uversionBookID'];
            $this->setChapterAndVerses();
        }
    }

    public function setFromImport($import)
    {
        $this->entry = $import->entry;
        $this->bookName = $import->bookName;
        $this->bookID = $import->bookID;
        $this->uversionBookID = $import->uversionBookID;
        $this->bookNumber = $import->bookNumber;
        $this->testament = $import->testament;
        $this->chapterStart = $import->chapterStart;
        $this->verseStart = $import->verseStart;
        $this->chapterEnd = $import->chapterEnd;
        $this->verseEnd = $import->verseEnd;
    }

    // Private helpers for entry management
    private function checkEntrySpacing(string $entry)
    {
        $entry = trim($entry);
        if (strpos($entry, ' ') === false) {
            $firstNumber = mb_strlen($entry);
            for ($i = 0; $i <= 9; $i++) {
                $pos = mb_strpos($entry, $i);
                if ($pos !== false && $pos < $firstNumber) {
                    $firstNumber = $pos;
                }
            }
            $book = mb_substr($entry, 0, $firstNumber);
            $chapter = mb_substr($entry, $firstNumber);
            $entry = $book . ' ' . $chapter;
        }
        $this->entry = $entry;
    }

    private function setBookName(){
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
    private function setChapterAndVerses()
    {
        $pass = str_replace($this->bookName, '', $this->entry);
        $pass = str_replace([' ', '፡'], ['', ':'], $pass); // Handles Amharic colon symbol
        $i = strpos($pass, ':');
        if ($i === false) {
            $this->chapterStart = trim($pass);
            $this->verseStart = 1;
            $this->verseEnd = 999;
        } else {
            $this->chapterStart = substr($pass, 0, $i);
            $verses = substr($pass, $i + 1);
            $i = strpos($verses, '-');
            if ($i !== false) {
                $this->verseStart = substr($verses, 0, $i);
                $this->verseEnd = substr($verses, $i + 1);
            } else {
                $this->verseStart = $verses;
                $this->verseEnd = $verses;
            }
        }
    }







    // Getters
    public function getBookID()
    {
        return $this->bookID;
    }

    public function getBookName()
    {
        return $this->bookName;
    }

    public function getBookNumber()
    {
        return $this->bookNumber;
    }

    public function getChapterStart()
    {
        return $this->chapterStart;
    }

    public function getEntry()
    {
        return $this->entry;
    }

    public function getLanguageCodeHL()
    {
        return $this->languageCodeHL;
    }
    public function getLanguageCodeIso()
    {
        return $this->languageCodeIso;
    }

    // Public export/import
    public function getPublic()
    {
        $export = new \stdClass();
        $export->entry = $this->entry;
        $export->bookName = $this->bookName;
        $export->bookID = $this->bookID;
        $export->uversionBookID = $this->uversionBookID;
        $export->bookNumber = $this->bookNumber;
        $export->testament = $this->testament;
        $export->chapterStart = $this->chapterStart;
        $export->verseStart = $this->verseStart;
        $export->chapterEnd = $this->chapterEnd;
        $export->verseEnd = $this->verseEnd;
        return $export;
    }

    public function getTestament()
    {
        return $this->testament;
    }

    public function getUversionBookID()
    {
        return $this->uversionBookID;
    }

    public function getVerseEnd()
    {
        return $this->verseEnd;
    }

    public function getVerseRange()
    {
        return $this->verseEnd - $this->verseStart;
    }

    public function getVerseStart()
    {
        return $this->verseStart;
    }
}
