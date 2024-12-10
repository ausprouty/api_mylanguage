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
use Exception;


/**
 * Service to fetch Bible passages from BibleGateway and process them.
 *
 * @note Uses a modified version of `simple_html_dom`:
 *       - Added `public $optional_closing_array = [];` to prevent warnings.
 */
class BibleGatewayPassageService extends AbstractBiblePassageService
{
    /**
     * Constructs the passage URL for BibleGateway.
     *
     * @return string The URL for fetching the passage.
     */
    public function getPassageUrl(): string
    {
        $reference = str_replace(' ', '%20', $this->passageReference->getEntry());
        $passageUrl = BibleGatewayConnectionService::getBaseUrl();
        $passageUrl .= '/passage/?search=' . $reference;
        $passageUrl .= '&version=' . $this->bible->getExternalId();
        return $passageUrl;
    }

    /**
     * Retrieves the webpage content of the Bible passage.
     *
     * @return array The passage content as an array.
     */
    public function getWebPage(): array
    {
        $reference = str_replace(' ', '%20', $this->passageReference->getEntry());
        $passageUrl = '/passage/?search=' . $reference;
        $passageUrl .= '&version=' . $this->bible->getExternalId();

        $passage = new BibleGatewayConnectionService($passageUrl);
        return [$passage->response];
    }

    /**
     * Extracts and processes the text of the Bible passage.
     *
     * @return string The formatted passage text.
     */
    public function getPassageText(): string
    {
        require_once(Config::getDir('paths.libraries') . 'simplehtmldom_1_9_1/simple_html_dom.php');

        $html = str_get_html($this->webpage[0]);
        if (!$html) {
            return '';
        }

        // Find the main passage text container.
        $startDiv = $html->find('div.passage-text', 0);
        if (!$startDiv) {
            return '';
        }

        // Extract relevant content.
        $cleanedHtml = $startDiv->outertext;

        // Remove footnotes if present.
        $endDiv = $html->find('div.footnotes', 0);
        if ($endDiv) {
            $endPos = strpos($cleanedHtml, $endDiv->outertext);
            if ($endPos !== false) {
                $cleanedHtml = substr($cleanedHtml, 0, $endPos);
            }
        }

        // Further clean and process the HTML content.
        $cleanedHtml = $this->removeSupTags($cleanedHtml);
        $cleanedHtml = str_get_html($cleanedHtml);

        // Remove links, unwanted spans, superscripts, headings, and process small caps.
        foreach ($cleanedHtml->find('a') as $link) {
            $link->outertext = '';
        }

        foreach ($cleanedHtml->find('span') as $span) {
            $class = $span->class ?? '';
            if (!in_array($class, ['chapternum', 'versenum'])) {
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
            $smallCaps->outertext = '<span style="font-variant: small-caps"';
            $smallCaps->outertext .= ' class="small-caps">' . $smallCaps->innertext;
            $smallCaps->outertext .= '</span>';
        }

        $finalOutput = $cleanedHtml->outertext;

        // Clear memory and return the balanced HTML content.
        $cleanedHtml->clear();
        unset($cleanedHtml);

        return $this->balanceDivTags($finalOutput);
    }

    /**
     * Extracts the passage reference in the local language.
     *
     * @return string The reference in the local language.
     * @throws Exception If HTML parsing fails.
     */
    public function getReferenceLocalLanguage(): string
    {
        require_once(Config::getDir('paths.libraries') . 'simplehtmldom_1_9_1/simple_html_dom.php');

        $webpage = preg_replace('/<script.*?<\/script>/is', '', $this->webpage[0]);
        $webpage = preg_replace('/<style.*?<\/style>/is', '', $webpage);

        $html = str_get_html($webpage, false, false, DEFAULT_TARGET_CHARSET, false, -1, false, DEFAULT_BR_TEXT);
        if (!$html) {
            throw new Exception("Failed to parse HTML");
        }

        // Attempt to locate the title element.
        $title = $html->find('div.passage-display', 0)
            ?? $html->find('h1.passage-display', 0)
            ?? $html->find('div.dropdown-display-text', 0);

        $reference = $title ? $title->plaintext : '';

        $html->clear();
        unset($html);
        return $reference;
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
        $dom->loadHTML(
            '<!DOCTYPE html><html><body>' . $html . '</body></html>',
            LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD
        );

        libxml_clear_errors();

        $balancedHtml = '';
        foreach ($dom->getElementsByTagName('body')->item(0)->childNodes as $node) {
            $balancedHtml .= $dom->saveHTML($node);
        }

        return mb_decode_numericentity($balancedHtml, $convmap, 'UTF-8');
    }
}
