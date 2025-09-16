<?php

namespace App\Services\BiblePassage;

use App\Factories\BibleGatewayConnectionFactory; // ⬅ inject this
use App\Services\BiblePassage\AbstractBiblePassageService;
use App\Configuration\Config;

class BibleGatewayPassageService extends AbstractBiblePassageService
{
    public function __construct(
        private BibleGatewayConnectionFactory $bg // ⬅ factory, not connection
    ) {}

    /** Resolve base URL from config; fallback to public site. */
    private function baseUrl(): string
    {
        return rtrim((string) Config::get('endpoints.biblegateway', 'https://www.biblegateway.com'), '/');
    }

    /**
     * Example: https://www.biblegateway.com/passage/?search=John%203%3A16&version=NIV
     */
    public function getPassageUrl(): string
    {
        $reference = $this->passageReference->getEntry();
        $version   = $this->bible->getExternalId();

        return $this->baseUrl()
            . '/passage/?search=' . rawurlencode($reference)
            . '&version=' . rawurlencode($version);
    }

    /**
     * Fetch the HTML for the passage from BibleGateway.
     */
    public function getWebPage(): string
    {
        // Build only the path/query; the factory/service will prepend base URL.
        $endpoint = '/passage/?search='
            . rawurlencode($this->passageReference->getEntry())
            . '&version=' . rawurlencode($this->bible->getExternalId());

        // ✅ use the factory (autoFetch=true, salvageJson=false for HTML)
        $conn = $this->bg->fromPath($endpoint, autoFetch: true, salvageJson: false);

        $body = $conn->getBody();
        if ($body === '') {
            throw new \RuntimeException("Empty response from BibleGateway: {$endpoint}");
        }
        return $body;
    }

    /**
     * Extract and clean the passage HTML block from the fetched page.
     */
    public function getPassageText(): string
    {
        $t0 = microtime(true);
        $html = (string)($this->webpage ?? '');
        $inBytes = strlen($html);
        \App\Services\LoggerService::logInfo('PassageText:start', "in_bytes={$inBytes}");

        if ($inBytes === 0) {
            \App\Services\LoggerService::logError('PassageText:empty', 'No HTML input');
            return '';
        }

        libxml_use_internal_errors(true);
        $flags = LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET | LIBXML_COMPACT | (defined('LIBXML_BIGLINES') ? LIBXML_BIGLINES : 0);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ok  = @$dom->loadHTML($html, $flags);
        \App\Services\LoggerService::logInfo('PassageText:parse_ms', (string) ((microtime(true) - $t0) * 1000));

        if (!$ok) {
            \App\Services\LoggerService::logError('PassageText:parse_fail', 'DOMDocument->loadHTML failed');
            return '';
        }

        $xp = new \DOMXPath($dom);
        $nl = $xp->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' passage-text ')]");
        if ($nl->length === 0) {
            \App\Services\LoggerService::logError('PassageText:no_container', 'div.passage-text not found');
            return '';
        }

        $newDom = new \DOMDocument('1.0', 'UTF-8');
        $passageDiv = $newDom->importNode($nl->item(0), true);
        $newDom->appendChild($passageDiv);
        $xp2 = new \DOMXPath($newDom);

        foreach ($xp2->query("//div[contains(concat(' ', normalize-space(@class), ' '), ' footnotes ')]|//script|//style|//noscript|//template|//sup|//h3|//a") as $n) {
            $n->parentNode->removeChild($n);
        }

        // unwrap spans except chapternum/versenum
        foreach ($xp2->query("//span[not(contains(concat(' ', normalize-space(@class), ' '), ' chapternum ')) and not(contains(concat(' ', normalize-space(@class), ' '), ' versenum '))]") as $n) {
            $p = $n->parentNode;
            while ($n->firstChild) $p->insertBefore($n->firstChild, $n);
            $p->removeChild($n);
        }

        // <small-caps> -> <span style="font-variant: small-caps">
        foreach ($xp2->query('//small-caps') as $n) {
            $span = $newDom->createElement('span');
            $span->setAttribute('class', 'small-caps');
            $span->setAttribute('style', 'font-variant: small-caps');
            while ($n->firstChild) $span->appendChild($n->firstChild);
            $n->parentNode->replaceChild($span, $n);
        }

        $out = $newDom->saveHTML($newDom->documentElement);
        \App\Services\LoggerService::logInfo('PassageText:done', "ms=" . (int)((microtime(true) - $t0) * 1000) . " out_bytes=" . strlen($out));
        return $out;
    }

    public function getReferenceLocalLanguage(): string
    {
        $t0 = microtime(true);
        $html = (string)($this->webpage ?? '');
        if ($html === '') {
            \App\Services\LoggerService::logError('RefLocal:empty', 'No HTML input');
            return '';
        }

        $html = preg_replace('~<(script|style|noscript|template)\b[^>]*>.*?</\1>~is', '', $html);

        libxml_use_internal_errors(true);
        $flags = LIBXML_NOERROR | LIBXML_NOWARNING | LIBXML_NONET | LIBXML_COMPACT | (defined('LIBXML_BIGLINES') ? LIBXML_BIGLINES : 0);

        $dom = new \DOMDocument('1.0', 'UTF-8');
        $ok  = @$dom->loadHTML($html, $flags);
        \App\Services\LoggerService::logInfo('RefLocal:parse_ms', (string)((microtime(true) - $t0) * 1000));

        if (!$ok) {
            \App\Services\LoggerService::logError('RefLocal:parse_fail', 'DOMDocument->loadHTML failed');
            return '';
        }

        $xp = new \DOMXPath($dom);
        $xpaths = [
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' passage-display ')]",
            "//h1[contains(concat(' ', normalize-space(@class), ' '), ' passage-display ')]",
            "//div[contains(concat(' ', normalize-space(@class), ' '), ' dropdown-display-text ')]",
        ];

        foreach ($xpaths as $q) {
            $nl = $xp->query($q);
            if ($nl && $nl->length > 0) {
                $txt = trim(preg_replace('/\s+/u', ' ', $nl->item(0)->textContent ?? ''));
                if ($txt !== '') return $txt;
            }
        }

        if (preg_match('~<(?:div|h1)[^>]*class="[^"]*(?:passage-display|dropdown-display-text)[^"]*"[^>]*>(.*?)</(?:div|h1)>~is', $html, $m)) {
            $text = strip_tags($m[1]);
            return trim(preg_replace('/\s+/u', ' ', html_entity_decode($text, ENT_QUOTES | ENT_HTML5, 'UTF-8')));
        }

        return '';
    }
}
