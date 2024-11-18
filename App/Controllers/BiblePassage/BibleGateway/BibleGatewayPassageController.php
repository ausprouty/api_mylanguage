<?php

namespace App\Controllers\BiblePassage\BibleGateway;

use App\Models\Bible\BiblePassageModel;
use App\Models\Bible\BibleReferenceModel;
use App\Models\Bible\BibleModel;
use App\Repositories\BiblePassageRepository;
use App\Services\Web\WebsiteConnectionService;

class BibleGatewayPassageController
{
    private $biblePassageRepository;
    private $bibleReference;
    private $bible;

    public function __construct(
        BiblePassageRepository $biblePassageRepository,
        BibleReferenceModel $bibleReference,
        BibleModel $bible
    ) {
        $this->biblePassageRepository = $biblePassageRepository;
        $this->bibleReference = $bibleReference;
        $this->bible = $bible;
    }

    public function fetchAndSavePassage(): void
    {
        $referenceShaped = str_replace(
            ' ',
            '%20',
            $this->bibleReference->getEntry()
        );

        $passageUrl = 'https://biblegateway.com/passage/?search=' .
            $referenceShaped . '&version=' . $this->bible->getExternalId();

        $webpage = new WebsiteConnectionService($passageUrl);
        if ($webpage->response) {
            $biblePassage = new BiblePassageModel();
            $biblePassage->setPassageText(
                $this->formatExternal($webpage->response)
            );
            $biblePassage->setReferenceLocalLanguage(
                $this->getReferenceLocalLanguage($webpage->response)
            );
            $biblePassage->setPassageUrl($passageUrl);

            $this->biblePassageRepository->savePassageRecord($biblePassage);
        }
    }

    private function getReferenceLocalLanguage(string $webpage): string
    {
        require_once(ROOT_LIBRARIES . '/simplehtmldom_1_9_1/simple_html_dom.php');

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

    private function formatExternal(string $webpage): string
    {
        require_once(ROOT_LIBRARIES . '/simplehtmldom_1_9_1/simple_html_dom.php');

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

    private function removeSupTags(string $htmlContent): string
    {
        $pattern = '/<sup[^>]*data-fn[^>]*>.*?<\/sup>/is';
        return preg_replace($pattern, '', $htmlContent);
    }

    private function balanceDivTags(string $html): string
    {
        libxml_use_internal_errors(true);
        $html = mb_convert_encoding($html, 'HTML-ENTITIES', 'UTF-8');
        $dom = new \DOMDocument('1.0', 'UTF-8');
        $dom->loadHTML($html, LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
        libxml_clear_errors();
        return $dom->saveHTML();
    }
}
