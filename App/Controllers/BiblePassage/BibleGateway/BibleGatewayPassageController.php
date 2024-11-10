<?php

namespace App\Controllers\BiblePassage\BibleGateway;

use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;
use App\Models\Bible\BiblePassageModel;
use App\Services\WebsiteConnectionService;
use App\Services\Database\DatabaseService;
use simple_html_dom;

class BibleGatewayPassageController extends BiblePassageModel
{
    private $databaseService;
    private $bibleReferenceInfo;
    private $bible;

    public function __construct(
        DatabaseService $databaseService,
        BibleReferenceInfoModel $bibleReferenceInfo,
        BibleModel $bible
    ) {
        $this->databaseService = $databaseService;
        $this->bibleReferenceInfo = $bibleReferenceInfo;
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
        return str_replace(' ', '%20', $this->bibleReferenceInfo->getEntry());
    }

    private function formatExternal($webpage)
    {
        require_once(ROOT_LIBRARIES . '/simplehtmldom_1_9_1/simple_html_dom.php');
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
        $bibleText = $this->stripDivTags($bibleText);
        $bibleText = preg_replace('/\bid="[^"]+"/', '', $bibleText);
        $bibleText = preg_replace('/class="text [^"]+">/', 'class="text">', $bibleText);
        $bibleText = preg_replace('/<span\s+class\s*=\s*["\']text["\']>(.+?)<\/span>/', '$1', $bibleText);
        $bibleText = preg_replace('/<h3\b[^>]*>(.*?)<\/h3>/si', '', $bibleText);

        return $bibleText;
    }

    private function replaceSmallCaps($bibleText)
    {
        $bibleText = preg_replace('/<span\s+style\s*=\s*["\']font-variant:\s*small-caps["\']\s+class\s*=\s*["\']small-caps["\']>(.+?)<\/span>/', '<small-caps>$1</small-caps>', $bibleText);
        $bibleText = str_ireplace('</small-caps>', '</span>', $bibleText);
        $bibleText = str_ireplace('<small-caps>', '<span style="font-variant: small-caps" class="small-caps">', $bibleText);
        return $bibleText;
    }

    private function stripDivTags($bibleText)
    {
        $bibleText = str_replace(['<!--end of crossrefs-->', '</div>'], '', $bibleText);
        return preg_replace('/<div\b[^>]*>/', '', $bibleText);
    }

    private function createLocalReference($websiteReference)
    {
        $expectedInReference = $this->bibleReferenceInfo->getChapterStart() . ':' . $this->bibleReferenceInfo->getVerseStart() . '-' . $this->bibleReferenceInfo->getVerseEnd();

        if (strpos($websiteReference, $expectedInReference) === false) {
            $lastSpace = strrpos($websiteReference, ' ');
            $websiteReference = substr($websiteReference, 0, $lastSpace) . ' ' . $expectedInReference;
        }

        $this->referenceLocalLanguage = $websiteReference;
    }
}
