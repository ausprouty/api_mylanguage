<?php

namespace App\Controllers\BiblePassage;

use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;
use App\Services\Database\DatabaseService;
use App\Services\WebsiteConnectionService;
use PDO;

class BibleYouVersionPassageController
{
    private $databaseService;
    private $bibleReferenceInfo;
    private $bible;
    private $bookName;
    public $response;
    private $chapterAndVerse;
    private $passageUrl;
    private $referenceLocalLanguage = '';

    public function __construct(
        DatabaseService $databaseService, 
        BibleReferenceInfoModel $bibleReferenceInfo, 
        BibleModel $bible
    ) {
        $this->databaseService = $databaseService;
        $this->bibleReferenceInfo = $bibleReferenceInfo;
        $this->bible = $bible;
        
        $this->setPassageUrl();
        $this->setChapterAndVerse();
        $this->setReferenceLocalLanguage();
    }

    public function getPassageText()
    {
        return $this->response;
    }

    public function getPassageUrl()
    {
        return $this->passageUrl;
    }

    public function getReferenceLocalLanguage()
    {
        return $this->referenceLocalLanguage;
    }

    private function setPassageUrl()
    {
        $uversionBibleBookID = $this->bibleReferenceInfo->getUversionBookID();
        $bibleBookAndChapter = "{$uversionBibleBookID}.{$this->bibleReferenceInfo->getChapterStart()}.";
        $bibleBookAndChapter .= "{$this->bibleReferenceInfo->getVerseStart()}-{$this->bibleReferenceInfo->getVerseEnd()}";
        $this->passageUrl = 'https://www.bible.com/bible/' . str_replace('%', $bibleBookAndChapter, $this->bible->getExternalId());
    }

    private function setChapterAndVerse()
    {
        $this->chapterAndVerse = "{$this->bibleReferenceInfo->getChapterStart()}:";
        $this->chapterAndVerse .= "{$this->bibleReferenceInfo->getVerseStart()}-{$this->bibleReferenceInfo->getVerseEnd()}";
    }

    private function setReferenceLocalLanguage()
    {
        $this->retrieveBookName();
        $this->referenceLocalLanguage = "{$this->bookName} {$this->chapterAndVerse}";
    }

    private function retrieveBookName()
    {
        $query = "SELECT name FROM bible_book_names WHERE languageCodeHL = :languageCodeHL AND bookID = :bookID LIMIT 1";
        $params = [
            ':languageCodeHL' => $this->bibleReferenceInfo->getLanguageCodeHL(),
            ':bookID' => $this->bibleReferenceInfo->getBookID(),
        ];

        $result = $this->databaseService->executeQuery($query, $params);
        $this->bookName = $result->fetch(PDO::FETCH_COLUMN);

        if (!$this->bookName) {
            $this->bookName = $this->fetchExternalBookName();
            if ($this->bookName) {
                $this->saveBookName();
            }
        }
    }

    private function fetchExternalBookName()
    {
        $webpageContent = $this->fetchExternalContent();

        if ($shortenedContent = $this->extractContentBeforeVerse($webpageContent)) {
            return $this->parseBookNameFromContent($shortenedContent);
        }

        return $this->parseBookNameFallback($webpageContent);
    }

    private function fetchExternalContent()
    {
        $url = $this->getExternalUrl();
        $webpage = new WebsiteConnectionService($url);
        return $webpage->response;
    }

    private function getExternalUrl()
    {
        $uversionBibleBookID = $this->bibleReferenceInfo->getUversionBookID();
        $bibleBookAndChapter = "{$uversionBibleBookID}.{$this->bibleReferenceInfo->getChapterStart()}.";
        $bibleBookAndChapter .= "{$this->bibleReferenceInfo->getVerseStart()}-{$this->bibleReferenceInfo->getVerseEnd()}";
        $chapter = str_replace('%', $bibleBookAndChapter, $this->bible->getExternalId());
        return 'https://www.bible.com/bible/' . str_replace(' ', '%20', $chapter);
    }

    private function extractContentBeforeVerse($webpageContent)
    {
        $endPosition = strpos($webpageContent, $this->chapterAndVerse);
        if ($endPosition !== false) {
            return substr($webpageContent, 0, $endPosition);
        }
        return null;
    }

    private function parseBookNameFromContent($content)
    {
        $start = strrpos($content, '"') + 1;
        return trim(substr($content, $start));
    }

    private function parseBookNameFallback($webpageContent)
    {
        $find = 'class="book-chapter-text">';
        $startPos = strpos($webpageContent, $find);

        if ($startPos !== false) {
            $endPos = strpos($webpageContent, '</h1>', $startPos);
            $bookName = trim(substr($webpageContent, $startPos + strlen($find), $endPos - $startPos));
            return $bookName;
        }

        return null;
    }

    private function saveBookName()
    {
        $query = "INSERT INTO bible_book_names (bookId, languageCodeHL, name) VALUES (:bookId, :languageCodeHL, :name)";
        $params = [
            ':bookId' => $this->bibleReferenceInfo->getBookID(),
            ':languageCodeHL' => $this->bible->getLanguageCodeHL(),
            ':name' => $this->bookName,
        ];

        $this->databaseService->executeQuery($query, $params);
    }
}
