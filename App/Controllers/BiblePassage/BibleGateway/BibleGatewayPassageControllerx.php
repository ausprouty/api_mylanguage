<?php

namespace App\Controllers\BiblePassage\BibleGateway;

use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageReferenceModel;
use App\Models\Bible\PassageModel;
use App\Services\Web\WebsiteConnectionService;
use App\Services\Database\DatabaseService;
use App\Configuration\Config;
use simple_html_dom;

class BibleGatewayPassageController extends PassageModel
{
    private $databaseService;
    private $bibleReference;
    private $bible;

    public function __construct(
        DatabaseService $databaseService,
        PassageReferenceModel $bibleReference,
        BibleModel $bible
    ) {
        $this->databaseService = $databaseService;
        $this->bibleReference = $bibleReference;
        $this->bible = $bible;
        $this->referenceLocalLanguage = '';
        $this->passageText = '';
        $this->passageUrl = '';
        $this->dateLastUsed = '';
        $this->dateChecked = '';
        $this->timesUsed = 0;
        $this->getExternal();
    }

    public function getExternal()
    {
        $referenceShaped = $this->shapeReference();
        $this->passageUrl = 'https://biblegateway.com/passage/?search=' . $referenceShaped . '&version=' . $this->bible->getExternalId();

        $webpage = new WebsiteConnectionService($this->passageUrl);
        $this->passageText = $webpage->response ? $this->formatExternal($webpage->response) : null;
    }

    private function shapeReference()
    {
        return str_replace(' ', '%20', $this->bibleReference->getEntry());
    }

    private function formatExternal($webpage)
    {
        require_once Config::get('ROOT_LIBRARIES') . 'simplehtmldom_1_9_1/simple_html_dom.php';
        $html = str_get_html($webpage);

        if (!$this->findAndSetLocalReference($html)) {
            return null;
        }

        $bibleText = $this->extractBibleText($html);
        $html->clear();

        $bibleText = $this->cleanBibleText($bibleText);
        $this->passageText = $this->finalizeBibleText($bibleText);

        return $this->passageText;
    }

    private function findAndSetLocalReference($html)
    {
        $referenceElement = $html->find('.dropdown-display-text', 0);
        if (!$referenceElement) {
            return false;
        }
        $this->createLocalReference($referenceElement->innertext);
        return true;
    }

    private function extractBibleText($html)
    {
        $bibleText = '';
        foreach ($html->find('.passage-text') as $passage) {
            $bibleText .= $passage;
        }
        return $bibleText;
    }

    private function cleanBibleText($bible)
    {
        $html = str_get_html($bible);
        $this->removeElements($html, [
            'a', 'div.footnotes', 'a.full-chap-link', 'sup.footnote', 'div.crossrefs.hidden', 'sup.crossreference', 'span.citation', 'div.il-text'
        ]);
        $this->replaceChapterNumbersWithVerse($html);

        return $html->outertext;
    }

    private function removeElements($html, $selectors)
    {
        foreach ($selectors as $selector) {
            foreach ($html->find($selector) as $element) {
                $element->outertext = '';
            }
        }
    }

    private function replaceChapterNumbersWithVerse($html)
    {
        foreach ($html->find('span.chapternum') as $chapter) {
            $chapter->outertext = '<sup class="versenum">1&nbsp;</sup>';
        }
    }

    private function finalizeBibleText($bibleText)
    {
        $bibleText = $this->replaceSmallCaps($bibleText);
        $bibleText = $this->stripDivTags($bibleText)
