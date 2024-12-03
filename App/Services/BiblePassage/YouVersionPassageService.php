<?php

namespace App\Services\BiblePassage;

use App\Services\BiblePassage\AbstractBiblePassageService;
use App\Services\LoggerService;
use App\Services\Web\YouVersionConnectionService;

/**
 * Handles interaction with YouVersion Bible passages.
 * Manages URL creation, content retrieval, and reference localization.
 */
class YouVersionPassageService extends AbstractBiblePassageService
{
    /**
     * Constructs the URL for a Bible passage on YouVersion.
     *
     * The `externalId` contains a placeholder `%`, replaced with
     * `$bibleBookAndChapter`, which includes book ID, chapter, and
     * verse details. Example URL format:
     * `https://www.bible.com/bible/{formattedExternalId}`
     *
     * @return string Fully constructed passage URL.
     */
    public function getPassageUrl(): string
    {
        $uversionBibleBookID = 
            $this->passageReference->getUversionBookID();
        $bibleBookAndChapter = "{$uversionBibleBookID}." .
            "{$this->passageReference->getChapterStart()}." .
            "{$this->passageReference->getVerseStart()}-" .
            "{$this->passageReference->getVerseEnd()}";

        $formatted = str_replace('%', 
            $bibleBookAndChapter, 
            $this->bible->getExternalId()
        );

        $lastDotPosition = strrpos($formatted, '.');
        if ($lastDotPosition !== false) {
            $beforeDot = 
                substr($formatted, 0, $lastDotPosition + 1);
            $afterDot = 
                substr($formatted, $lastDotPosition + 1);
            $formatted = $beforeDot . rawurlencode($afterDot);
        }

        return $formatted;
    }

    /**
     * Fetches external content from a given URL.
     *
     * @param string $url URL to fetch content from.
     * @return string[] Raw content retrieved from the URL.
     */
    public function getWebPage(): array
    {
        $endpoint = $this->passageUrl;
        $output = [];
        $webpage = new YouVersionConnectionService($endpoint);

        if (!$webpage) {
            LoggerService::logError(
                'Failed to fetch Bible passage from YouVersion.'
            );
            return $output;
        }

        $output[0] = $webpage->response;
        return $output;
    }

    /**
     * Retrieves full text of the Bible passage.
     *
     * Extracts JSON data embedded in the webpage, decodes it,
     * and retrieves passage text and reference.
     *
     * @return string Full text of the passage.
     */
    public function getPassageText(): string
    {
        $html = $this->webpage[0];
        $pos_start = strpos($html, 'verses":[{"reference"');
        $pos_end = strpos($html, '"twitterCard":', $pos_start);

        if ($pos_start !== false && $pos_end !== false) {
            $length = $pos_end - $pos_start - 1;
            $extracted_text = substr($html, $pos_start, $length);
        } else {
            LoggerService::logError(
                'Error extracting passage text.'
            );
            return '';
        }

        $json_string = '{"' . $extracted_text . '}';
        $data = json_decode($json_string);

        if (json_last_error() === JSON_ERROR_NONE) {
            $this->referenceLocalLanguage = 
                $data->verses[0]->reference->human . PHP_EOL;
            return $data->verses[0]->content . PHP_EOL;
        } else {
            LoggerService::logError(
                'Invalid JSON: ' . json_last_error_msg()
            );
            return '';
        }
    }

    /**
     * Retrieves localized reference for the passage.
     *
     * @return string Localized reference (e.g., book name and verses).
     */
    public function getReferenceLocalLanguage(): string
    {
        return $this->referenceLocalLanguage;
    }
}
