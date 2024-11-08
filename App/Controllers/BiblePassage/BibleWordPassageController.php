<?php

namespace App\Controllers\BiblePassage;


use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BiblePassageModel as BiblePassageModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;

class BibleWordPassageController extends BiblePassageModel {

    private $bibleReferenceInfo;
    private $bible;


    public function __construct( BibleReferenceInfoModel $bibleReferenceInfo, BibleModel $bible){
 
        $this->bibleReferenceInfo=$bibleReferenceInfo;
        $this->bible = $bible;
        $this->referenceLocalLanguage = '';
        $this->passageText = '';
        $this->createPassageUrl();
        $this->dateLastUsed = '';
        $this->dateChecked = '';
        $this->timesUsed = 0;
        $this->getExternal();
    }

    public function getReferenceLocalLanguage(){
        return $this->referenceLocalLanguage;
    }
    public function getPassageText(){
        return $this->passageText;
    }
    public function getPassageURL(){
        return $this->passageUrl;
    }


    private function createPassageUrl(){
       //sample: https://wordproject.org/bibles/am/43/1.htm
       $this->passageUrl =  'https://wordproject.org/bibles/' .  $this->bible->getExternalId()  .'/';
       $this->passageUrl .= $this->chapterPage();
       $this->passageUrl .= '.htm';
    }
 
    private function chapterPage(){
       $bookNumber =  $this->bibleReferenceInfo->getBookNumber();
       if (intval( $bookNumber)<10){
        $bookNumber = '0' .  $bookNumber;
       }
       $chapterNumber = $this->bibleReferenceInfo->getChapterStart();
       return $bookNumber . '/'.  $chapterNumber;
    }
    public function getExternal(){
       $dir = ROOT_RESOURCES . 'bibles/wordproject/';
       $externalId = $this->bible->getExternalId();
       $bibleDir = $dir .  $externalId. '/'.  $externalId . '/';
       $fileName = $bibleDir . $this->chapterPage();
       writeLog('BibleWordPassageController', 'getExternal: ' . $fileName);    
       $webpage = null;
       if (file_exists ($fileName . '.html')){
            $webpage = file_get_contents($fileName . '.html');
       }
       elseif (file_exists ($fileName . '.htm')){
            $webpage = file_get_contents($fileName . '.htm');
        }
       if ($webpage){
            $this->formatExternal($webpage);
            $this->setReferenceLocalLanguage($webpage);
       }
    }

    private function setReferenceLocalLanguage($webpage){
        // <p class="ym-noprint"> Hoofstuk:    
        $find = '<p class="ym-noprint">';
        $posStart = strpos($webpage, $find) + strlen($find);
        $posEnd = strpos($webpage, ':', $posStart);
        $length = $posEnd-$posStart;
        $bookName = trim (substr($webpage, $posStart, $length));
        $verses = $this->bibleReferenceInfo->getChapterStart() . ':';
        $verses .=  $this->bibleReferenceInfo->getVerseStart() . '-'. $this->bibleReferenceInfo->getVerseEnd();
        $this->referenceLocalLanguage = $bookName . ' '. $verses;

    }


    private function formatExternal($webpage){
        $cleanPage = $this->cleanPage($webpage);
        $this->passageText =   "\n" . '<!-- begin bible -->';
        $this->passageText .=   $this->selectVerses($cleanPage) ."\n" ;
        $this->passageText .=  '<!-- end bible -->' . "\n" ;
        $this->referenceLocalLanguage = $this->createReferenceLocalLanguage($cleanPage);
    }
    private function cleanPage($webpage){
        $find = '<!--... the Word of God:-->'; // trim off front
        $pos = strpos($webpage, $find) + strlen($find);
        $webpage = substr($webpage, $pos);
        $find = '<!--... sharper than any twoedged sword... -->'; // trim off end
        $pos = strpos($webpage, $find);
        $clean = substr($webpage, 0,  $pos);
        return $clean;

    }
    private function selectVerses($page){
        $text = '';
        $firstVerse = intval($this->bibleReferenceInfo->getVerseStart());
        $lastVerse = intval($this->bibleReferenceInfo->getVerseEnd());
        $bad = array('<p>', '</p>');
        $page = str_replace($bad, '', $page);
        $bad = array ('<br/>', '<br />');
        $page = str_replace( $bad, '<br>', $page);
        $lines = explode('<br>', $page);
        foreach ($lines as $line){
            $posStart = strpos($line, '>') +1;
            $find = '</span>';
            $posEnd = stripos($line, $find);
            $length = $posEnd - $posStart;
            $verseNum = intval(substr($line, $posStart, $length));
            if ($verseNum >= $firstVerse && $verseNum <= $lastVerse ){
                $text .= '<p><sup>'.$verseNum . '</sup>';
                $text .= substr($line, $posEnd + strlen($find));
                $text .= '</p>' . "\n";
            }
        }
        return $text;
    }
    private function createReferenceLocalLanguage($cleanPage){
        $expectedInReference = $this->bibleReferenceInfo->getChapterStart() . ':' .
            $this->bibleReferenceInfo->getVerseStart() . '-' . $this->bibleReferenceInfo->getVerseEnd();

        //if (strpos($websiteReference, $expectedInReference) == FALSE){
        //    $lastSpace =strrpos($websiteReference, ' ');
        //    $websiteReference = substr($websiteReference,0, $lastSpace) .' '. $expectedInReference;
        //}
       // $this->referenceLocalLanguage = $websiteReference;
    }

   

}