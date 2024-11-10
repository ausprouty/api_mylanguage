<?php

namespace App\Services\Bible;

use App\Models\Data\BibleBrainConnectionModel;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;

class BibleBrainPassageService
{
    private $bible;
    private $bibleReferenceInfo;
    public $response;

    public function __construct(BibleModel $bible, BibleReferenceInfoModel $bibleReferenceInfo)
    {
        $this->bible = $bible;
        $this->bibleReferenceInfo = $bibleReferenceInfo;
    }

    public function fetchAndFormatPassage($languageCodeIso, $bibleReferenceInfo)
    {
        $this->fetchPassageData();
        return $this->formatPassageText();
    }

    private function fetchPassageData()
    {
        $url = 'https://4.dbt.io/api/bibles/filesets/' . $this->bible->getExternalId();
        $url .= '/' . $this->bibleReferenceInfo->getBookID() . '/' . $this->bibleReferenceInfo->getChapterStart();
        $url .= '?verse_start=' . $this->bibleReferenceInfo->getVerseStart() . '&verse_end=' . $this->bibleReferenceInfo->getVerseEnd();

        $passage = new BibleBrainConnectionModel($url);
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
        return $this->getBookNameLocalLanguage() . ' ' . $this->bibleReferenceInfo->getChapterStart() . ':' .
               $this->bibleReferenceInfo->getVerseStart() . '-' . $this->bibleReferenceInfo->getVerseEnd();
    }

    private function getBookNameLocalLanguage()
    {
        if (!isset($this->response->data)) {
            return $this->bibleReferenceInfo->getBookName();
        }

        return $this->response->data[0]->book_name_alt ?? $this->bibleReferenceInfo->getBookName();
    }
}
