<?php

namespace App\Controllers\BiblePassage\BibleGateway;

use App\Models\Bible\BiblePassageModel;
use App\Models\Bible\BibleReferenceModel;
use App\Models\Bible\BibleModel;
use App\Repositories\BiblePassageRepository;
use App\Services\Web\BibleGatewayConnectionService;
use App\Services\LoggerService;
use App\Configuration\Config;

/**
 * Controller to fetch Bible passages from BibleGateway
 * and save them to the database.
 */
class BibleGatewayPassageController
{
    private $biblePassageRepository;
    private $bibleReference;
    private $bible;

    public function __construct(
        BibleReferenceModel $bibleReference,
        BibleModel $bible,
        BiblePassageRepository $biblePassageRepository,
    ) {
        $this->biblePassageRepository = $biblePassageRepository;
        $this->bibleReference = $bibleReference;
        $this->bible = $bible;
    }

    /**
     * Fetches a Bible passage from BibleGateway and saves it to the database.
     */
    public function fetchAndSavePassage(): BiblePassageModel
    {
        $referenceShaped = str_replace(
            ' ',
            '%20',
            $this->bibleReference->getEntry()
        );

        $passageUrl = '/passage/?search=' .
            $referenceShaped . '&version=' . $this->bible->getExternalId();
        print_r($passageUrl);
        print_r('<br><hr><br>');
        flush();
        $webpage = new BibleGatewayConnectionService($passageUrl);

        $biblePassageModel = new BiblePassageModel();
        if ($webpage->response) {

            $biblePassageModel->setPassageText(
                $this->formatExternal($webpage->response)
            );
            $biblePassageModel->setReferenceLocalLanguage(
                $this->getReferenceLocalLanguage($webpage->response)
            );
            $biblePassageModel->setPassageUrl($passageUrl);

            $this->biblePassageRepository->savePassageRecord($biblePassageModel);
        }
        return $biblePassageModel;
    }

    /**
     * Extracts the reference in the local language from the webpage.
     *
     * @param string $webpage The HTML content of the webpage.
     * @return string The local reference.
     */
    private function getReferenceLocalLanguage(string $webpage): string
    {
        require_once(Config::get('ROOT_LIBRARIES') . '/simplehtmldom_1_9_1/simple_html_dom.php');

        $html = str_get_html($webpage);
        if (!$html) {
            return '';
        }

        $title = $html->find('h1.passage-display-bcv', 0);
        $localReference = $title ? $title->plaintext : '';

        $html->clear();
        unset($html);

        return $localReference;
    }

    /**
     * Formats the external HTML content into cleaned text.
     *
     * @param string $webpage The HTML content of the webpage.
     * @return string The cleaned passage text.
     */
    private function formatExternal(string $webpage): string
    {
        require_once(Config::get('ROOT_LIBRARIES') . '/simplehtmldom_1_9_1/simple_html_dom.php');

        $html = str_get_html($webpage);
        if (!$html) {
            return '';
        }

        $startDiv = $html->find('div.passage-text', 0);
        $cleanedHtml = $startDiv ? $startDiv->outertext : '';

        $endDiv = $html->find('div.footnotes', 0);
        if ($endDiv) {
            $endPosition = strpos($cleanedHtml, $endDiv->outertext);
            if ($endPosition !== false) {
                $cleanedHtml = substr($cleanedHtml, 0, $endPosition);
            }
        }

        $cleanedHtml = $this->removeSupTags($cleanedHtml);
        $cleanedHtml = str_get_html($cleanedHtml);

        foreach ($cleanedHtml->find('a') as $link) {
            $link->outertext = '';
        }

        foreach ($cleanedHtml->find('span') as $span) {
            $class = $span->class ?? '';
            if ($class !== 'chapternum' && $class !== 'versenum') {
                $span->outertext = $span->innertext;
            }
        }

        foreach ($cleanedHtml->find('sup') as $sup) {
            $sup->outertext = '';
        }

        foreach ($cleanedHtml->find('h3') as $heading) {
            $heading->outertext = '';
        }

        foreach ($cleanedHtml->find('small-caps') as $smallCaps) {
            $smallCaps->outertext =
                '<span style="font-variant: small-caps" class="small-caps">' .
                $smallCaps->innertext . '</span>';
        }

        $finalOutput = $cleanedHtml->outertext;

        $cleanedHtml->clear();
        unset($cleanedHtml);



        return $this->balanceDivTags($finalOutput);
    }

    /**
     * Removes specific <sup> tags from the HTML content.
     *
     * @param string $htmlContent The HTML content.
     * @return string The cleaned content.
     */
    private function removeSupTags(string $htmlContent): string
    {
        $pattern = '/<sup[^>]*data-fn[^>]*>.*?<\/sup>/is';
        return preg_replace($pattern, '', $htmlContent);
    }

    /**
     * Balances any unclosed or mismatched <div> tags in the HTML content.
     *
     * @param string $html The HTML content.
     * @return string The balanced HTML content.
     */
    private function balanceDivTags(string $html): string
    {
        // Use libxml error handling
        libxml_use_internal_errors(true);

        // Detect the current encoding of the $html
        $encoding = mb_detect_encoding($html, ['UTF-8', 'ISO-8859-1', 'Windows-1252'], true);
        print_r("<br><hr>Line 183 BibleGatewayConnectionService<br>");
        flush();
        print_r($html);
        flush();
        print_r("<br><hr><br>");
        flush();
        flush();

        // Convert to UTF-8 if it's not already
        if ($encoding !== 'UTF-8') {
            $html = mb_convert_encoding($html, 'UTF-8', $encoding);
        }

        // Create a DOMDocument instance and load the HTML
        $dom = new \DOMDocument('1.0', 'UTF-8');

        // Suppress warnings during loadHTML
        $dom->loadHTML(
            '<!DOCTYPE html><html><body>' . $html . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        // Clear libxml errors
        libxml_clear_errors();

        // Extract the inner HTML of the body tag
        $body = $dom->getElementsByTagName('body')->item(0);

        // Debugging: Show the body content
        print_r("<br><hr>Line 214 BibleGatewayConnectionService<br>");
        if ($body) {
            echo $dom->saveHTML($body); // Show only the HTML content of the body
        } else {
            echo 'No body tag found.';
        }
        print_r("<br><hr><br>");
        flush();
        print_r($dom->saveHTML($body));
        flush();

        // Return the inner HTML or the original HTML if body is not found
        return $body ? $dom->saveHTML($body) : $html;
    }
}
