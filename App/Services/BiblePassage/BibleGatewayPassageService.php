<?php

namespace App\Services\BiblePassage;

use App\Models\Bible\PassageModel;
use App\Models\Bible\PassageReferenceModel;
use App\Models\Bible\BibleModel;
use App\Repositories\BiblePassageRepository;
use App\Services\Web\BibleGatewayConnectionService;
use App\Services\LoggerService;
use App\Configuration\Config;
use App\Services\BiblePassage\AbstractBiblePassageService;
use DOMDocument;
use DOMXPath;
use Exception;

/**
 * Service to fetch Bible passages from BibleGateway
 * and process them for saving to the database.
 *
 * @note This class uses a modified version of `simple_html_dom`.
 *       Modification:
 *       - Added `public $optional_closing_array = [];` to the `simple_html_dom` class.
 *       Reason:
 *       - To avoid deprecated warnings in PHP 8.2+ about dynamic property creation.
 *       If upgrading the library, ensure this modification is reapplied.
 */
class BibleGatewayPassageService extends AbstractBiblePassageService
{
    /**
     * Constructs the passage URL for fetching from BibleGateway.
     *
     * @return void
     */
    public function getPassageUrl(): string
    {
        $referenceShaped = str_replace(' ', '%20', $this->passageReference->getEntry());
        $passageUrl = BibleGatewayConnectionService::getBaseUrl();
        $passageUrl .= '/passage/?search=' . $referenceShaped . '&version=' . $this->bible->getExternalId();
        return $passageUrl;
    }

    /**
     * Retrieves the webpage content of the Bible passage.
     *
     * @return void
     */
    public function getWebPage():array
    {
        $referenceShaped = str_replace(' ', '%20', $this->passageReference->getEntry());
        $passageUrl = '/passage/?search=' . $referenceShaped . '&version=' . $this->bible->getExternalId();

        $passage = new BibleGatewayConnectionService($passageUrl);
        $output = [];
        $output[0] = $passage->response;
        return $output;
    }

    /**
     * Extracts and processes the text of the Bible passage.
     *
     * @return void
     */
    public function getPassageText(): string
    {
        require_once(ROOT_LIBRARIES . '/simplehtmldom_1_9_1/simple_html_dom.php');

        $html = str_get_html($this->webpage[0]);
        if (!$html) {
            return null;
        }

        $startDiv = $html->find('div.passage-text', 0);
        if (!$startDiv) {
            echo "No passage-text found.";
            return null;
        }

        $cleanedHtml = $startDiv->outertext;

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
            $smallCaps->outertext = '<span style="font-variant: small-caps" class="small-caps">' .
                $smallCaps->innertext . '</span>';
        }

        $finalOutput = $cleanedHtml->outertext;

        $cleanedHtml->clear();
        unset($cleanedHtml);

        return $this->balanceDivTags($finalOutput);
    }

    /**
     * Extracts the Bible passage reference in the local language from the webpage.
     *
     * @return void
     * @throws Exception If HTML parsing fails.
     *
     * @note This method relies on a custom modification to `simple_html_dom`:
     *       Added `public $optional_closing_array = [];` to prevent PHP 8.2+ deprecation warnings.
     *       Ensure this change persists if the library is updated.
     */
 
    public function getReferenceLocalLanguage(): string
    {
        require_once(Config::get('ROOT_LIBRARIES') . '/simplehtmldom_1_9_1/simple_html_dom.php');
        $webpage = $this->webpage[0];
        $webpage = preg_replace('/<script.*?<\/script>/is', '', $webpage);
        $webpage = preg_replace('/<style.*?<\/style>/is', '', $webpage);
        $html = str_get_html($webpage, false, false, DEFAULT_TARGET_CHARSET, false, -1, false, DEFAULT_BR_TEXT);
        if (!$html) {
            throw new Exception("Failed to parse HTML");
        }

        $title = $html->find('div.passage-display', 0)
            ?? $html->find('h1.passage-display', 0)
            ?? $html->find('div.dropdown-display-text', 0);

        $referenceLocalLanguage = $title ? $title->plaintext : '';

        $html->clear();
        unset($html);
        return  $referenceLocalLanguage;
    }

    /**
     * Removes specific <sup> tags from the HTML content.
     *
     * @param string $htmlContent The raw HTML content.
     * @return string The cleaned HTML content.
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
        libxml_use_internal_errors(true);

        $convmap = [0x80, 0xFFFF, 0, 0xFFFF];
        $html = mb_encode_numericentity($html, $convmap, 'UTF-8');

        $dom = new DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML('<!DOCTYPE html><html><body>' . $html . '</body></html>', LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);

        libxml_clear_errors();

        $balancedHtml = '';
        foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $balancedHtml .= $dom->saveHTML($node);
        }

        return mb_decode_numericentity($balancedHtml, $convmap, 'UTF-8');
    }
}
