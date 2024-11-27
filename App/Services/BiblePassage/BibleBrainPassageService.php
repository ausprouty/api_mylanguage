<?php

namespace App\Services\BiblePassage;

use App\Services\Web\BibleBrainConnectionService;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceModel;

class BibleBrainPassageService
{
    private $bible;
    private $bibleReference;
    public $response;

    public function __construct(
        BibleModel $bible,
        BibleReferenceModel $bibleReference
    ) {
        $this->bible = $bible;
        $this->bibleReference = $bibleReference;
    }

    public function fetchAndFormatPassage()
    {
        $this->fetchPassageData();
        return $this->formatPassageText();
    }

    private function fetchPassageData()
    {
        $url = '    ' . $this->bible->getIdBibleGateway();
        $url .= '/' . $this->bibleReference->getBookID() . '/' . $this->bibleReference->getChapterStart();
        $url .= '?verse_start=' . $this->bibleReference->getVerseStart() . '&verse_end=' . $this->bibleReference->getVerseEnd();
        echo '<pre>';
        var_export ($url);
        echo '</pre>';
        $passage = new BibleBrainConnectionService($url);
        $this->response = $passage->response;
    }

    public function formatPassageText()
    {
        $text = null;
        $multiVerseLine = false;
        $startVerseNumber = null;

        if (!isset($this->response->data)) {
            return null;
        }

        foreach ($this->response->data as $verse) {
            if (!isset($verse->verse_text)) {
                return null;
            }

            $verseNum = $verse->verse_start_alt;
            if ($multiVerseLine) {
                $multiVerseLine = false;
                $verseNum = $startVerseNumber . '-' . $verse->verse_end_alt;
            }

            if ($verse->verse_text == '-') {
                $multiVerseLine = true;
                $startVerseNumber = $verse->verse_start_alt;
            }

            if ($verse->verse_text != '-') {
                $text .= '<p><sup class="versenum">' . $verseNum . '</sup> ' . $verse->verse_text . '</p>';
            }
        }

        return $text;
    }

    public function setReferenceLocalLanguage()
    {
        return $this->getBookNameLocalLanguage() . ' ' . $this->bibleReference->getChapterStart() . ':' .
            $this->bibleReference->getVerseStart() . '-' . $this->bibleReference->getVerseEnd();
    }

    private function getBookNameLocalLanguage()
    {
        if (!isset($this->response->data)) {
            return $this->bibleReference->getBookName();
        }

        return $this->response->data[0]->book_name_alt ?? $this->bibleReference->getBookName();
    }
}
