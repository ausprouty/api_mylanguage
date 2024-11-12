<?php

namespace App\Controllers\BiblePassage;

use App\Models\Bible\BibleModel;
use App\Models\Bible\BiblePassageModel;
use App\Models\Bible\BibleReferenceInfoModel;
use App\Configuration\Config;

class BibleWordPassageController
{
    private $bibleReferenceInfo;
    private $bible;
    private $referenceLocalLanguage = '';
    private $passageText = '';
    private $passageUrl = '';
    private $dateLastUsed = '';
    private $dateChecked = '';
    private $timesUsed = 0;

    public function __construct(BibleReferenceInfoModel $bibleReferenceInfo, BibleModel $bible)
    {
        $this->bibleReferenceInfo = $bibleReferenceInfo;
        $this->bible = $bible;
        $this->passageUrl = $this->createPassageUrl();
        $this->getExternal();
    }

    public function getReferenceLocalLanguage()
    {
        return $this->referenceLocalLanguage;
    }

    public function getPassageText()
    {
        return $this->passageText;
    }

    public function getPassageURL()
    {
        return $this->passageUrl;
    }

    private function createPassageUrl()
    {
        return 'https://wordproject.org/bibles/' . $this->bible->getExternalId() . '/' . $this->formatChapterPage() . '.htm';
    }

    private function formatChapterPage()
    {
        $bookNumber = str_pad($this->bibleReferenceInfo->getBookNumber(), 2, '0', STR_PAD_LEFT);
        $chapterNumber = $this->bibleReferenceInfo->getChapterStart();
        return $bookNumber . '/' . $chapterNumber;
    }

    private function getExternal()
    {
        $filePath = $this->generateFilePath();
        writeLog('BibleWordPassageController', 'getExternal: ' . $filePath);

        if ($webpage = $this->loadWebpageContent($filePath)) {
            $this->passageText = $this->formatExternalText($webpage);
            $this->referenceLocalLanguage = $this->extractReferenceLanguage($webpage);
        }
    }

    private function generateFilePath()
    {
        $baseDir = Config::get('ROOT_RESOURCES') . 'bibles/wordproject/';
        $externalId = $this->bible->getExternalId();
        return $baseDir . $externalId . '/' . $externalId . '/' . $this->formatChapterPage();
    }

    private function loadWebpageContent($filePath)
    {
        if (file_exists($filePath . '.html')) {
            return file_get_contents($filePath . '.html');
        } elseif (file_exists($filePath . '.htm')) {
            return file_get_contents($filePath . '.htm');
        }
        return null;
    }

    private function extractReferenceLanguage($webpage)
    {
        $find = '<p class="ym-noprint">';
        $posStart = strpos($webpage, $find) + strlen($find);
        $posEnd = strpos($webpage, ':', $posStart);
        $bookName = trim(substr($webpage, $posStart, $posEnd - $posStart));

        $verses = $this->bibleReferenceInfo->getChapterStart() . ':' .
            $this->bibleReferenceInfo->getVerseStart() . '-' . $this->bibleReferenceInfo->getVerseEnd();

        return $bookName . ' ' . $verses;
    }

    private function formatExternalText($webpage)
    {
        $cleanedPage = $this->cleanHtmlContent($webpage);
        $selectedVerses = $this->selectVerses($cleanedPage);

        return "\n<!-- begin bible -->" . $selectedVerses . "\n<!-- end bible -->\n";
    }

    private function cleanHtmlContent($webpage)
    {
        $startMarker = '<!--... the Word of God:-->';
        $endMarker = '<!--... sharper than any twoedged sword... -->';

        $startPos = strpos($webpage, $startMarker) + strlen($startMarker);
        $endPos = strpos($webpage, $endMarker);

        return substr($webpage, $startPos, $endPos - $startPos);
    }

    private function selectVerses($page)
    {
        $page = str_replace(['<p>', '</p>', '<br/>', '<br />'], ['', '', '<br>'], $page);
        $lines = explode('<br>', $page);

        $verseRange = range(
            intval($this->bibleReferenceInfo->getVerseStart()),
            intval($this->bibleReferenceInfo->getVerseEnd())
        );

        $verses = '';
        foreach ($lines as $line) {
            $verseNum = $this->extractVerseNumber($line);
            if (in_array($verseNum, $verseRange)) {
                $verses .= $this->formatVerseLine($verseNum, $line);
            }
        }

        return $verses;
    }

    private function extractVerseNumber($line)
    {
        $startPos = strpos($line, '>') + 1;
        $endPos = stripos($line, '</span>');
        return intval(substr($line, $startPos, $endPos - $startPos));
    }

    private function formatVerseLine($verseNum, $line)
    {
        $startPos = stripos($line, '</span>') + strlen('</span>');
        $verseText = substr($line, $startPos);
        return '<p><sup>' . $verseNum . '</sup>' . $verseText . '</p>' . "\n";
    }
}
