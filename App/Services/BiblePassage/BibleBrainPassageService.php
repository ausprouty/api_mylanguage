<?php

namespace App\Services\BiblePassage;

use App\Services\Web\BibleBrainConnectionService;
use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageReferenceModel;

class BibleBrainPassageService extends AbstractBiblePassageService
{



    public function getPassageText(): string
    {
        // Implement logic to fetch passage text from BibleBrain
        return "BibleBrain passage text";
    }

    public function getPassageUrl(): string
    {
        // Implement logic to fetch passage URL
        return "https://biblebrain.example.com/passage";
    }

    public function getReferenceLocalLanguage(): string
    {
        // Implement logic to fetch reference in local language
        return "BibleBrain reference in local language";
    }




    public function fetchAndFormatPassage()
    {
        $this->fetchPassageData();
        return $this->formatPassageText();
    }

    private function fetchPassageData()
    {
        $url = '    ' . $this->bible->getIdBibleGateway();
        $url .= '/' . $this->passageReference->getBookID() . '/' . $this->passageReference->getChapterStart();
        $url .= '?verse_start=' . $this->passageReference->getVerseStart() . '&verse_end=' . $this->passageReference->getVerseEnd();
        echo '<pre>';
        var_export($url);
        echo '</pre>';
        $passage = new BibleBrainConnectionService($url);
        $this->response = $passage->response;
    }

    public function formatPassageText()
    {
        $text = null;
        $multiVerseLine = false;
        $startVerseNumber = null;

        if (!isset($this->response->data)) {
            return null;
        }

        foreach ($this->response->data as $verse) {
            if (!isset($verse->verse_text)) {
                return null;
            }

            $verseNum = $verse->verse_start_alt;
            if ($multiVerseLine) {
                $multiVerseLine = false;
                $verseNum = $startVerseNumber . '-' . $verse->verse_end_alt;
            }

            if ($verse->verse_text == '-') {
                $multiVerseLine = true;
                $startVerseNumber = $verse->verse_start_alt;
            }

            if ($verse->verse_text != '-') {
                $text .= '<p><sup class="versenum">' . $verseNum . '</sup> ' . $verse->verse_text . '</p>';
            }
        }

        return $text;
    }

    public function setReferenceLocalLanguage()
    {
        return $this->getBookNameLocalLanguage() . ' ' . $this->passageReference->getChapterStart() . ':' .
            $this->passageReference->getVerseStart() . '-' . $this->passageReference->getVerseEnd();
    }

    private function getBookNameLocalLanguage()
    {
        if (!isset($this->response->data)) {
            return $this->passageReference->getBookName();
        }

        return $this->response->data[0]->book_name_alt ?? $this->passageReference->getBookName();
    }
}
