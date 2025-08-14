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
     * @return passage content as an arraystring.
     */
    public function getWebPage(): string
    {
        $reference = str_replace(' ', '%20', $this->passageReference->getEntry());
        $passageUrl = '/passage/?search=' . $reference;
        $passageUrl .= '&version=' . $this->bible->getExternalId();
        // Fetch
        $conn = new BibleGatewayConnectionService($passageUrl);
        // Return the HTML body (string), as the signature requires
        $body = $conn->getBody();
        if ($body === '') {
            throw new \RuntimeException("Empty response from BibleGateway: {$passageUrl}");
        }
        return $body;
    }

    /**
     * Extracts and processes the text of the Bible passage.
     *
     * @return string The formatted passage text.
     */
    /**
 * Extracts and processes the text of the Bible passage.
 *
 * @return string The formatted passage HTML.
    */
    public function getPassageText(): string
    {
        $t0 = microtime(true);
        $inBytes = strlen($this->webpage ?? '');
        \App\Services\LoggerService::logInfo(
            'PassageText:start',
            "in_bytes={$inBytes}"
        );

        if ($inBytes === 0) {
            \App\Services\LoggerService::logError(
                'PassageText:empty',
                'No HTML input'
            );
            return '';
        }

        // Parse the HTML with libxml (fast, safe).
        libxml_use_internal_errors(true);
        $flags = LIBXML_NOERROR
            | LIBXML_NOWARNING
            | LIBXML_NONET
            | LIBXML_COMPACT
            | (defined('LIBXML_BIGLINES') ? LIBXML_BIGLINES : 0);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ok = @$dom->loadHTML($this->webpage, $flags);

        $parseMs = (int) ((microtime(true) - $t0) * 1000);
        \App\Services\LoggerService::logInfo(
            'PassageText:parse_ms',
            (string) $parseMs
        );

        if (!$ok) {
            \App\Services\LoggerService::logError(
                'PassageText:parse_fail',
                'DOMDocument->loadHTML failed'
            );
            return '';
        }

        $xp = new \DOMXPath($dom);
        // Find the main passage container
        $nl = $xp->query(
            "//div[contains(concat(' ', normalize-space(@class), ' '),"
            . " ' passage-text ')]"
        );
        if ($nl->length === 0) {
            \App\Services\LoggerService::logError(
                'PassageText:no_container',
                'div.passage-text not found'
            );
            return '';
        }

        // Work in a new tiny DOM that only contains the passage div.
        $newDom = new \DOMDocument('1.0', 'UTF-8');
        $passageDiv = $newDom->importNode($nl->item(0), true);
        $newDom->appendChild($passageDiv);
        $xp2 = new \DOMXPath($newDom);

        // Remove footnotes if present inside the passage chunk.
        foreach ($xp2->query(
            "//div[contains(concat(' ', normalize-space(@class), ' '),"
            . " ' footnotes ')]"
        ) as $n) {
            $n->parentNode->removeChild($n);
        }

        // Strip heavy/useless nodes early.
        foreach ($xp2->query('//script|//style|//noscript|//template') as $n) {
            $n->parentNode->removeChild($n);
        }

        // Remove superscripts and headings.
        foreach ($xp2->query('//sup|//h3') as $n) {
            $n->parentNode->removeChild($n);
        }

        // Remove links entirely (matches your prior behavior).
        foreach ($xp2->query('//a') as $n) {
            $n->parentNode->removeChild($n);
        }

        // Unwrap spans EXCEPT chapternum/versenum.
        $spans = $xp2->query(
            "//span[not(contains(concat(' ', normalize-space(@class), ' '),"
            . " ' chapternum ')) and "
            . "not(contains(concat(' ', normalize-space(@class), ' '),"
            . " ' versenum '))]"
        );
        foreach ($spans as $n) {
            $parent = $n->parentNode;
            while ($n->firstChild) {
                $parent->insertBefore($n->firstChild, $n);
            }
            $parent->removeChild($n);
        }

        // <small-caps> → styled <span>.
        foreach ($xp2->query('//small-caps') as $n) {
            $span = $newDom->createElement('span');
            $span->setAttribute('class', 'small-caps');
            $span->setAttribute('style', 'font-variant: small-caps');
            while ($n->firstChild) {
                $span->appendChild($n->firstChild);
            }
            $n->parentNode->replaceChild($span, $n);
        }

        $cleanMs = (int) ((microtime(true) - $t0) * 1000);
        \App\Services\LoggerService::logInfo(
            'PassageText:clean_ms',
            (string) $cleanMs
        );

        // Serialize only the passage div.
        $out = $newDom->saveHTML($newDom->documentElement);
        $outBytes = strlen($out);

        $totalMs = (int) ((microtime(true) - $t0) * 1000);
        \App\Services\LoggerService::logInfo(
            'PassageText:done',
            "ms={$totalMs} out_bytes={$outBytes}"
        );

        return $out;
    }


   
    /**
     * Extracts the passage reference in the local language.
     *
     * @return string The reference in the local language.
     */
    public function getReferenceLocalLanguage(): string
    {
        $t0 = microtime(true);

        // Ensure we have a string (your pipeline should make $this->webpage a string).
        $html = is_array($this->webpage) ? (string)($this->webpage[0] ?? '') : (string)$this->webpage;
        $inBytes = strlen($html);

        \App\Services\LoggerService::logInfo('RefLocal:start', "in_bytes={$inBytes}");
        if ($inBytes === 0) {
            \App\Services\LoggerService::logError('RefLocal:empty', 'No HTML input');
            return '';
        }

        // Optional: strip heavy blocks to speed DOM parse on big pages
        $html = preg_replace('~<(script|style|noscript|template)\b[^>]*>.*?</\1>~is', '', $html);

        libxml_use_internal_errors(true);
        $flags = LIBXML_NOERROR
            | LIBXML_NOWARNING
            | LIBXML_NONET
            | LIBXML_COMPACT
            | (defined('LIBXML_BIGLINES') ? LIBXML_BIGLINES : 0);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ok  = @$dom->loadHTML($html, $flags);

        $parseMs = (int)((microtime(true) - $t0) * 1000);
        \App\Services\LoggerService::logInfo('RefLocal:parse_ms', (string)$parseMs);

        if (!$ok) {
            \App\Services\LoggerService::logError('RefLocal:parse_fail', 'DOMDocument->loadHTML failed');
            return '';
        }

        $xp = new \DOMXPath($dom);

        // Try the likely containers, in order.
        $xpaths = [
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' passage-display ')]",
            "//h1[contains(concat(' ', normalize-space(@class), ' '), ' passage-display ')]",
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' dropdown-display-text ')]",
        ];

        $reference = '';
        foreach ($xpaths as $q) {
            $nl = $xp->query($q);
            if ($nl && $nl->length > 0) {
                $reference = trim(preg_replace('/\s+/u', ' ', $nl->item(0)->textContent ?? ''));
                if ($reference !== '') break;
            }
        }

        // Fallback: light regex if DOM/XPath didn’t find it (structure change?)
        if ($reference === '') {
            if (preg_match('~<(?:div|h1)[^>]*class="[^"]*(?:passage-display|dropdown-display-text)[^"]*"[^>]*>(.*?)</(?:div|h1)>~is', $html, $m)) {
                $text = strip_tags($m[1]);
                $reference = trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
                \App\Services\LoggerService::logInfo('RefLocal:fallback', 'regex_used=1');
            }
        }

        $totalMs = (int)((microtime(true) - $t0) * 1000);
        \App\Services\LoggerService::logInfo('RefLocal:done', "ms={$totalMs} found=" . ($reference !== '' ? '1' : '0'));

        return $reference;
    }

}