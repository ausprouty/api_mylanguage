<?php

namespace App\Services\BiblePassage;

use App\Factories\PassageFactory;
use App\Services\Web\BibleBrainConnectionService;
use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageReferenceModel;
use App\Services\BiblePassage\AbstractBiblePassageService;
use Mpdf\Tag\P;

class BibleBrainPassageService extends AbstractBiblePassageService
{
     // create like: https://live.bible.is/bible/AC1IBS/GEN/1
     //but this may not work -- maybe look for others vide stream?
    public function getPassageUrl():string
    {
        $passageUrl = 'https://live.bible.is/bible/';
        $passageUrl .= $this->bible->getExternalId() . '/';
        $passageUrl .= $this->passageReference->getuversionBookID() . '/' . 
            $this->passageReference->getChapterStart();
        return $passageUrl;
    }
     //to get verses: https://4.dbt.io/api/bibles/filesets/:fileset_id/:book/:chapter?verse_start=5&verse_end=5&v=4
     public function getWebpage(): array{
        $url = 'bibles/filesets/'. $this->bible->getExternalId();
        $url .= '/' . $this->passageReference->getBookID() . '/' . $this->passageReference->getChapterStart();
        $url .= '?verse_start=' . $this->passageReference->getVerseStart() . '&verse_end=' . $this->passageReference->getVerseEnd();
        $passage = new BibleBrainConnectionService($url);
        print_r ($passage->response->data);
        die();
        return $passage->response->data;
        
     }

     /* you are given an array:

     Array (
    [0] => stdClass Object (
        [book_id]         => ACT
        [book_name]       => Acts
        [book_name_alt]   => KISAH RASUI-RASUI
        [chapter]         => 1
        [chapter_alt]     => 1
        [verse_start]     => 3
        [verse_start_alt] => 3
        [verse_end]       => 3
        [verse_end_alt]   => 3
        [verse_text]      => Óh ka lheueh Gobnyan maté, treb jih na peuet ploh uroe 
                             Gobnyan kayém that geutunyok ngon cara nyang nyata 
                             that ubak murit-murit Gobnyan bahwa Gobnyan biet-biet 
                             udeb. Awaknyan jikalon Gobnyan, dan Gobnyan 
                             geumeututoe ngon awaknyan keuhai pakriban Allah geumat 
                             peurintah sibagoe Raja.
    )
)
    */
    public function getPassageText(): string
    {
        $text = '';
        foreach ($this->webpage as $item){
            if ($item->verse_start == $item->verse_end){
                $verse_number = $item->verse_start;
            }
            else{
                $verse_number = $item->verse_start . "-". $item->verse_end;
            }
            $text .= '<p>';
            $text .= '<sup class="versenum">'. $verse_number . '</sup>';
            $text .= $item->verse_text;
            $text .= '</p>';
        }// Implement logic to fetch passage text from BibleBrain
        return $text;
    }

    public function getReferenceLocalLanguage(): string
    {
        if (isset($this->webpage[0]) && isset($this->webpage[0]->book_name_alt)) {
            $book_name = $this->webpage[0]->book_name_alt;
            $referenceLocalLanguage = $book_name . ' ' . $this->passageReference->getChapterStart();
            $referenceLocalLanguage .= ':' . $this->passageReference->getVerseStart() . '-' . $this->passageReference->getVerseEnd();
        } else {
            // Handle the case where $this->webpage[0] or its properties are not set
            $referenceLocalLanguage = 'Unknown Reference'; // Or any fallback logic
        }
        return $referenceLocalLanguage;
    }
}  
