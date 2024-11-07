<?php

namespace App\Controllers\BiblePassage\BibleGateway;

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Models\Bible\BiblePassageModel as BiblePassageModel;
use App\Models\Data\WebsiteConnectionModel as WebsiteConnectionModel;


class BibleGatewayPassageController extends BiblePassageModel {

    private $bibleReferenceInfo;
    private $bible;


    public function __construct( BibleReferenceInfoModel $bibleReferenceInfo, BibleModel $bible){
 
        $this->bibleReferenceInfo=$bibleReferenceInfo;
        $this->bible = $bible;
        $this->referenceLocalLanguage = '';
        $this->passageText = '';
        $this->passageUrl = '';
        $this->dateLastUsed = '';
        $this->dateChecked = '';
        $this->timesUsed = 0;
        $this->getExternal();
    }
 

    public function getExternal(){
        /* it seems that Chinese does not always like the way we enter things.// try this and see if it works/
	    $reference_shaped = str_replace(
            $this->bibleReferenceInfo->bookName,
            $this->bibleReferenceInfo->bookID,
            $this->bibleReferenceInfo->entry
        );
        $reference_shaped = str_replace(' ', '%20', $reference_shaped);
*/
	    $reference_shaped = str_replace(' ' , '%20', $this->bibleReferenceInfo->getEntry());
        $this->passageUrl= 'https://biblegateway.com/passage/?search='. $reference_shaped . '&version='. $this->bible->getExternalId() ;
        $webpage = new WebsiteConnectionModel($this->passageUrl);
        if ($webpage->response){
            $this->passageText =  $this->formatExternal($webpage->response);
        }
        else{
            $this->passageText = null;
        }
    }
    private function formatExternal($webpage){
        require_once(ROOT_LIBRARIES . '/simplehtmldom_1_9_1/simple_html_dom.php');
        $html = str_get_html($webpage);
        $e = $html->find('.dropdown-display-text', 0);
        if (!$e){
            return null;
        }
        $this->createLocalReference($e->innertext);
        $passages = $html->find('.passage-text');
        $bible = '';
        foreach($passages as $passage){
            $bible .= $passage;
        }
        $html->clear();
        unset($html);
        //
        // now we are working just with Bible text
        //
        // change all small-caps so not cut by spans later
        $pattern = '/<span\s+style\s*=\s*["\']font-variant:\s*small-caps["\']\s+class\s*=\s*["\']small-caps["\']>(.+?)<\/span>/';
        $replacement = '<small-caps>$1</small-caps>';
        $bible = preg_replace($pattern, $replacement, $bible);
    
        $html = str_get_html($bible);
    
       

       
        // remove all links
        $ret = $html->find ('a');
        foreach ($ret as $href){
            $href->outertext= '';
        }
        // remove footnotes
        $ret= $html->find('div[class=footnotes]');
        foreach ($ret as $footnote){
            $footnote->outertext= '';
        }
        $html = str_get_html($bible);
        $ret = $html->find ('span[class=woj]');
        foreach ($ret as $span){
            $span->outertext= $span->innertext;
        }
        $bible = $html->outertext;

        $html->clear();
        $html = str_get_html($bible);
        // remove read chapter
        
        $ret= $html->find('a[class=full-chap-link]');
        foreach ($ret as $footnote){
            $footnote->outertext= '';
        }
        // remove  footnotes div
        $ret= $html->find('div[class=footnotes]');
        foreach ($ret as $footnote){
            $footnote->outertext= '';
        }
        // remove links to footnotes
        $ret= $html->find('sup[class=footnote]');
        foreach ($ret as $footnote){
            $footnote->outertext= '';
        }
        // remove crossreference div
        $ret= $html->find('div[class=crossrefs hidden]');
        foreach ($ret as $cross_reference){
            $cross_reference->outertext= '';
        }
        $ret= $html->find('sup[class=crossreference]');
        foreach ($ret as $cross_reference){
            $cross_reference->outertext= '';
        }
       
         // remove  citations
         $ret= $html->find('span[class=citation]');
         foreach ($ret as $citation){
             $citation->outertext= '';
         }
        $ret= $html->find('div[class=il-text]');
        foreach ($ret as $cross_reference){
            $cross_reference->outertext= '';
        }
        // change chapter number to verse 1
        // <span class="chapternum">53&nbsp;</span>
        $ret= $html->find('span[class=chapternum]');
        foreach ($ret as $chapter){
            $chapter->outertext= '<sup class="versenum">1&nbsp;</sup>';
        }
        $bible = $html->outertext;
        unset($html);
        $bad= array(
            '<!--end of crossrefs-->'
        );
        $good='';
        $bible= str_replace( $bad, $good, $bible);
        $pos_start = strpos($bible,'<p' );
        if ($pos_start !== FALSE){
            $bible = substr($bible, $pos_start);
           
        }
        // remove all remaining div marks
        $bible= str_ireplace('</div>', '', $bible);

        $bible=  preg_replace('/<div\b[^>]*>/', '', $bible);
        // remove id and 
        $pattern = '/\bid="[^"]+"/';
        $bible = preg_replace($pattern, '', $bible);
        '/<span class="text [^"]+">/';
        $pattern = '/class="text [^"]+">/';
        $bible = preg_replace($pattern, 'class="text">', $bible);
        // renove span cladd = text;
        $pattern = '/<span\s+class\s*=\s*["\']text["\']>(.+?)<\/span>/';
        $replacement = '$1';
        $bible = preg_replace($pattern, $replacement, $bible);
    
        // remove subheadings <h3>
        $bible = preg_replace('/<h3\b[^>]*>(.*?)<\/h3>/si', '', $bible);
        $bible = str_replace('<!--end of footnotes-->', '', $bible);
        // return small-caps as span
        $bible= str_ireplace('</small-caps>', '</span>', $bible);
        $bible= str_ireplace('<small-caps>', '<span style="font-variant: small-caps" class="small-caps">', $bible);

        return $bible;
    }
    private function createLocalReference($websiteReference){
        $expectedInReference = $this->bibleReferenceInfo->getChapterStart() . ':' .
            $this->bibleReferenceInfo->getVerseStart() . '-' . $this->bibleReferenceInfo->getVerseEnd();
        if (strpos($websiteReference, $expectedInReference) == FALSE){
            $lastSpace =strrpos($websiteReference, ' ');
            $websiteReference = substr($websiteReference,0, $lastSpace) .' '. $expectedInReference;
        }
        $this->referenceLocalLanguage =$websiteReference;
    }

}