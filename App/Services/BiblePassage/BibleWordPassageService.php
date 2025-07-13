<?php

namespace App\Services\BiblePassage;

use App\Services\BiblePassage\AbstractBiblePassageService;
use App\Services\Web\BibleWordConnectionService;
use App\Services\LoggerService;
use App\Configuration\Config;

/**
 * BibleWordPassageService handles Bible passage retrieval from WordProject.
 * It determines whether to fetch data from a local file or a web source and
 * processes the content for application use.
 */
class BibleWordPassageService extends AbstractBiblePassageService
{
    /**
     * Generates the URL for the passage. 
     * Currently returns a placeholder URL.
     *
     * @return string The passage URL.
     */
    public function getPassageUrl(): string
    {
        $url =  "https://wordproject.org/bibles/";
        $url .= $this->bible->getExternalId();
        $url .= '/' . $this->formatChapterPage() . '.htm';
        return $url;
    }

    /**
     * Retrieves the webpage content, deciding between local or remote sources.
     *
     * @return array The webpage content as an array.
     */
    public function getWebPage(): array
    {
        $webpage = [];
        $localFile = $this->generateFilePath();
        LoggerService::logInfo('BibleWordPassageService-37', $localFile);

        if (file_exists($localFile)) {
            $webpage[0] = $this->fetchFromFileDirectory($localFile);
        } else {
            $response = $this->fetchFromWeb();
            $webpage[0] = $response->response;
        }

        return $webpage;
    }

    /**
     * Generates the file path for a local resource.
     *
     * @return string The generated file path.
     */
    private function generateFilePath()
    {
        $baseDir = Config::getDir('resources.root') . 'bibles/wordproject/';
        $externalId = $this->bible->getExternalId();
        $filePath =  $baseDir . $externalId . '/' . $this->formatChapterPage() . '.html';
        LoggerService::logInfo('BibleWordPassageService-59', $filePath);
        return $filePath;
    }

    /**
     * Formats the chapter and page structure for the file path.
     *
     * @return string The formatted chapter and page.
     */
    private function formatChapterPage()
    {
        $bookNumber = $this->passageReference->getBookNumber();
        $bookNumber = str_pad($bookNumber, 2, '0', STR_PAD_LEFT);
        $chapterNumber = $this->passageReference->getChapterStart();
        return $bookNumber . '/' . $chapterNumber;
    }

    /**
     * Fetches content from an external source using a web service.
     *
     * @return BibleWordConnectionService The response from the web service.
     */
    public function fetchFromWeb()
    {
        $endpoint = $this->bible->getExternalId() . '/' . $this->formatChapterPage() . '.htm';
        $webpage = new BibleWordConnectionService($endpoint);

        if (!$webpage) {
            LoggerService::logError('BibleWordPassageService-84','Failed to fetch Bible passage from WordProject.');
            return null;
        }

        return $webpage;
    }

    /**
     * Reads content from a local file.
     *
     * @param string $filename The file path to read.
     * @return string The file content.
     */
    private function fetchFromFileDirectory($filename)
    {
         LoggerService::logInfo('BibleWordPassageService-102','Fetching file from local source');
        return file_get_contents($filename);
    }

    /**
     * Extracts and formats the passage text.
     *
     * @return string The formatted passage text.
     */
    public function getPassageText(): string
    {
        $chapter = $this->trimToChapter();
        $verses = $this->trimToVerses($chapter);
        return $verses;
    }

    /**
     * Cleans and extracts chapter content from HTML.
     *
     * @return string The cleaned chapter content.
     */
    private function trimToChapter(): string
    {
        $webpage = $this->webpage[0];
        $startMarker = '<!--... the Word of God:-->';
        $endMarker = '<!--... sharper than any twoedged sword... -->';
        $startPos = strpos($webpage, $startMarker) + strlen($startMarker);
        $endPos = strpos($webpage, $endMarker);
        return substr($webpage, $startPos, $endPos - $startPos);
    }

    /**
     * Formats and cleans a specific verse line.
     *
     * @param int $verseNum The verse number.
     * @param string $line The raw verse line.
     * @return string The formatted verse line.
     */
    private function formatVerseLine($verseNum, $line)
    {
        $lastSpanPos = strripos($line, '</span>');
        $verseText = $lastSpanPos !== false
            ? substr($line, $lastSpanPos + strlen('</span>'))
            : $line;

        return '<p><sup>' . $verseNum . '</sup>' . $verseText . '</p>' . "\n";
    }

    /**
     * Extracts the verse number from a line of text.
     *
     * @param string $line The line containing the verse.
     * @return int The verse number.
     */
    private function extractVerseNumber($line)
    {
        $endPos = strripos($line, '</span>');
        if ($endPos === false) {
            return 0;
        }

        $startPos = strrpos(substr($line, 0, $endPos), '>');
        if ($startPos === false) {
            return 0;
        }

        return intval(trim(substr($line, $startPos + 1, $endPos - $startPos - 1)));
    }

    /**
     * Cleans and selects verses from chapter content.
     *
     * @param string $webpage The cleaned chapter content.
     * @return string The selected verses.
     */
    private function trimToVerses($webpage)
    {
        $chapter = $this->trimToChapter($webpage);
        LoggerService::logInfo('BibleWordPassageService-180',$chapter);
        $selectedVerses = $this->selectVerses($chapter);
         LoggerService::logInfo('BibleWordPassageService-182',$selectedVerses);
         $result = '';
         if ($selectedVerses){
           $result = "\n<!-- begin bible -->" . $selectedVerses .
            "\n<!-- end bible -->\n";
        }
        return $result;
    }

    /**
     * Filters and formats verses from the chapter content.
     *
     * @param string $page The chapter content.
     * @return string The formatted verses.
     */
    private function selectVerses($page)
    {
        $page = str_replace(
            ['<!--span class="verse"', '<p>', '</p>', '<br/>', '<br />'],
            ['<span class="verse"', '', '', '<br>', '<br>'],
            $page
        );

        $verseRange = range(
            intval($this->passageReference->getVerseStart()),
            intval($this->passageReference->getVerseEnd())
        );

        $verses = '';
        foreach (explode('<br>', $page) as $line) {
            $verseNum = $this->extractVerseNumber($line);
            if (in_array($verseNum, $verseRange)) {
                $verses .= $this->formatVerseLine($verseNum, $line);
            }
        }

        return $verses;
    }

    /**
     * Extracts the local language reference from the webpage.
     *
     * @return string The extracted reference in local language.
     */
    public function getReferenceLocalLanguage(): string
    {
        $webpage = $this->webpage[0];
        $startMarker = '<!-- End of Display Options  -->';
        $endMarker = '<!--... the Word of God:-->';
        $startPos = strpos($webpage, $startMarker) + strlen($startMarker);
        $endPos = strpos($webpage, $endMarker);
        $webpage = substr($webpage, $startPos, $endPos - $startPos);

        $startMarker = '<h3>';
        $endMarker = '</h3>';
        $startPos = strpos($webpage, $startMarker) + strlen($startMarker);
        $endPos = strpos($webpage, $endMarker);
        $bookName = trim(substr($webpage, $startPos, $endPos - $startPos));

        return $bookName . ':' . $this->passageReference->getVerseStart() .
            '-' . $this->passageReference->getVerseEnd();
    }
}
