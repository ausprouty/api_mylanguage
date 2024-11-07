<?php


namespace App\Controller\BiblePassage\BibleBrain;

use App\Controller\BiblePassage\BibleBrain\BibleBrainPassageController;

class BibleBrainTextPlainController extends BibleBrainPassageController
{

    /*
    [2]=>
  object(stdClass)#18 (10) {
    ["book_id"]=>
    string(3) "LUK"
    ["book_name"]=>
    string(4) "Luke"
    ["book_name_alt"]=>
    string(28) "የሉቃስ ወንጌል።"
    ["chapter"]=>
    int(1)
    ["chapter_alt"]=>
    string(0) ""
    ["verse_start"]=>
    int(5)
    ["verse_start_alt"]=>
    string(1) "5"
    ["verse_end"]=>
    int(5)
    ["verse_end_alt"]=>
    string(1) "5"
    ["verse_text"]=>
    string(237) "በይሁዳ ንጉሥ በሄሮድስ ዘመን ከአብያ ክፍል የሆነ ዘካርያስ የሚባል አንድ ካህን ነበረ፤ ሚስቱም ከአሮን ልጆች ነበረች፥ ስምዋም ኤልሳቤጥ ነበረ።"
  }
  */

    public function formatPassageText()
    {   $text = null;
        $multiVerseLine = false;
        $startVerseNumber = null;
        if (!isset($this->response->data)){
            $this->passageText = NULL;
            return $this->passageText;
        }
        foreach ($this->response->data as $verse){
            if (!isset($verse->verse_text)){
                $text = NULL;
                break;
            }
            $verseNum = $verse->verse_start_alt;
            if ($multiVerseLine){
                $multiVerseLine = false;
                $verseNum = $startVerseNumber . '-' . $verse->verse_end_alt;
            }
            if ($verse->verse_text == '-'){
                $multiVerseLine = true;
                $startVerseNumber = $verse->verse_start_alt;
            }
            if ($verse->verse_text != '-') {
                $text .= '<p><sup class="versenum">' . $verseNum . '</sup> '. $verse->verse_text . '</p>';
            }

        }
         $this->passageText = $text;
        return $this->passageText;
    }

    public function setReferenceLocalLanguage(){
        $this->referenceLocalLanguage = $this->getBookNameLocalLanguage();
        $this->referenceLocalLanguage .= ' '. $this->bibleReferenceInfo->getChapterStart() . ':' .
            $this->bibleReferenceInfo->getVerseStart()  .'-' .$this->bibleReferenceInfo->getVerseEnd();
    }

    public function getReferenceLocalLanguage(){
        return $this->referenceLocalLanguage;
    }

    public function getBookNameLocalLanguage(){
        if (!isset($this->response->data)){
           return $this->bibleReferenceInfo->getBookName();
        }
        return $this->response->data[0]->book_name_alt;
    }
    
}