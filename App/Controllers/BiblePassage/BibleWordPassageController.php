<?php

namespace App\Controllers\BiblePassage;

use App\Configuration\Config;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BiblePassageModel;
use App\Models\Bible\BibleReferenceModel;
use App\Repositories\BiblePassageRepository;
use App\Services\LoggerService;
use App\Services\Web\BibleWordConnectionService;

class BibleWordPassageController
{
    private $bible;
    private $biblePassageRepository;
    private $bibleReference;

    /**
     * Constructor to initialize dependencies.
     *
     * @param BibleReferenceModel $bibleReference
     * @param BibleModel $bible
     * @param BiblePassageRepository $biblePassageRepository
     */
    public function __construct(
        BibleReferenceModel $bibleReference,
        BibleModel $bible,
        BiblePassageRepository $biblePassageRepository
    ) {
        $this->biblePassageRepository = $biblePassageRepository;
        $this->bibleReference = $bibleReference;
        $this->bible = $bible;
    }

    /**
     * Cleans a segment of HTML content between specific markers.
     *
     * @param string $webpage The HTML content to clean.
     * @return string The cleaned content.
     */
    private function trimToChapter($webpage)
    {  
        $startMarker = '<!--... the Word of God:-->';
        $endMarker = '<!--... sharper than any twoedged sword... -->';
        $startPos = strpos($webpage, $startMarker) + strlen($startMarker);
        $endPos = strpos($webpage, $endMarker);
        $chapter = substr($webpage, $startPos, $endPos - $startPos);       
        return $chapter;
    }

    

    /**
     * Extracts and formats a single verse line.
     *
     * @param int $verseNum The verse number.
     * @param string $line The verse line content.
     * @return string The formatted verse line.
     */
    private function formatVerseLine($verseNum, $line)
    {
        $startPos = stripos($line, '</span>') + strlen('</span>');
        $verseText = substr($line, $startPos);
        return '<p><sup>' . $verseNum . '</sup>' . $verseText . '</p>' . "\n";
    }

    /**
     * Extracts the local language reference from the webpage.
     *
     * @param string $webpage The HTML content.
     * @return string The extracted reference language.
     */
    private function extractReferenceLanguage($webpage)
    {
        $find = '<p class="ym-noprint">';
        $posStart = strpos($webpage, $find) + strlen($find);
        $posEnd = strpos($webpage, ':', $posStart);
        $bookName = trim(substr($webpage, $posStart, $posEnd - $posStart));

        $verses = $this->bibleReference->getChapterStart() . ':' .
            $this->bibleReference->getVerseStart() . '-' .
            $this->bibleReference->getVerseEnd();

        return $bookName . ' ' . $verses;
    }

    /**
     * Extracts the verse number from a line of text.
     *
     * @param string $line The line containing the verse.
     * @return int The extracted verse number.
     */
    private function extractVerseNumber($line)
    {
        $startPos = strpos($line, '>') + 1;
        $endPos = stripos($line, '</span>');
        return intval(substr($line, $startPos, $endPos - $startPos));
    }

    /**
     * Fetches content from an external source using a web service.
     *
     * @return BiblePassageModel The Bible passage model with data.
     */
    public function fetchFromWeb()
    {
        $biblePassageModel = new BiblePassageModel();
        $endpoint = $this->bible->getExternalId() . '/'
            . $this->formatChapterPage() . '.htm';

        $webpage = new BibleWordConnectionService($endpoint);
        
        if (!$webpage->response) {
            LoggerService::logError('Failed to fetch Bible passage from WordProject.');
            return $biblePassageModel;
        }
        $text = $this->trimToVerses($webpage->response);
        if (!$text) {
            LoggerService::logError('Unable to extract Bible Word Text.');
            return $biblePassageModel;
        }
        $biblePassageModel->setPassageText($text);
        $biblePassageModel->setReferenceLocalLanguage(
            $this->extractReferenceLanguage($webpage->response)
        );


        return $biblePassageModel;
    }

    /**
     * Fetches content from a local server file.
     *
     * @return BiblePassageModel The Bible passage model with data.
     */
    public function fetchFromServerFile()
    {
        $filePath = $this->generateFilePath();
        $webpage = $this->loadWebpageContent($filePath);
        $biblePassageModel = new BiblePassageModel();

        $text = $this->trimToVerses($webpage);
        if ($text) {
            $biblePassageModel->setPassageText($text);
            $biblePassageModel->setReferenceLocalLanguage(
                $this->extractReferenceLanguage($webpage)
            );
        }

        return $biblePassageModel;
    }

    /**
     * Formats the chapter and page structure for the URL or file path.
     *
     * @return string The formatted chapter and page.
     */
    private function formatChapterPage()
    {
        $bookNumber = $this->bibleReference->getBookNumber();
        if (strlen($bookNumber) === 1) {
            $bookNumber = str_pad($bookNumber, 2, '0', STR_PAD_LEFT);
        }
        $chapterNumber = $this->bibleReference->getChapterStart();
        return $bookNumber . '/' . $chapterNumber;
    }

    /**
     * Formats and cleans external text from the webpage.
     *
     * @param string $webpage The raw HTML content.
     * @return string The formatted passage text.
     */
    private function trimToVerses($webpage)
    {
        $chapter = $this->trimToChapter($webpage);
        $selectedVerses = $this->selectVerses($chapter);

        return "\n<!-- begin bible -->" . $selectedVerses .
            "\n<!-- end bible -->\n";
    }

    /**
     * Generates the file path for a local resource.
     *
     * @return string The generated file path.
     */
    private function generateFilePath()
    {
        $baseDir = Config::get('ROOT_RESOURCES') . 'bibles/wordproject/';
        $externalId = $this->bible->getExternalId();
        return $baseDir . $externalId . '/' . $externalId . '/'
            . $this->formatChapterPage();
    }

    /**
     * Loads webpage content from a file.
     *
     * @param string $filePath The path to the file.
     * @return string|null The file content or null if not found.
     */
    private function loadWebpageContent($filePath)
    {
        if (file_exists($filePath . '.html')) {
            return file_get_contents($filePath . '.html');
        } elseif (file_exists($filePath . '.htm')) {
            return file_get_contents($filePath . '.htm');
        }
        return null;
    }

    /**
     * Selects and formats verses from the cleaned webpage content.
     *
     * @param string $page The cleaned webpage content.
     * @return string The selected verses.
     */
    private function selectVerses($page)
    {
        
        $page = str_replace(
            ['<!--span class="verse"', '<p>', '</p>', '<br/>', '<br />'],
            ['<span class="verse"', '', '', '<br>', '<br>'],
            $page
        );
        $lines = explode('<br>', $page);
        print_r($lines);
        flush();

        $verseRange = range(
            intval($this->bibleReference->getVerseStart()),
            intval($this->bibleReference->getVerseEnd())
        );

        $verses = '';
        foreach ($lines as $line) {
            $verseNum = $this->extractVerseNumber($line);
            if (in_array($verseNum, $verseRange)) {
                $verses .= $this->formatVerseLine($verseNum, $line);
            }
        }

        print_r($verses);
        flush();
        return $verses;
    }
}
