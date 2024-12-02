<?php

namespace App\Services\BiblePassage;

use App\Factories\PassageFactory;
use App\Services\Web\BibleBrainConnectionService;
use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageReferenceModel;
use App\Services\BiblePassage\AbstractBiblePassageService;

/**
 * BibleBrainPassageService retrieves and formats Bible passage data from the
 * Bible Brain API.
 */
class BibleBrainPassageService extends AbstractBiblePassageService
{
    /**
     * Generate the passage URL for Bible Brain.
     * Example: https://live.bible.is/bible/AC1IBS/GEN/1
     *
     * @return string The URL to access the passage.
     */
    public function getPassageUrl(): string
    {
        $passageUrl = 'https://live.bible.is/bible/';
        $passageUrl .= $this->bible->getExternalId() . '/';
        $passageUrl .= $this->passageReference->getuversionBookID() . '/';
        $passageUrl .= $this->passageReference->getChapterStart();
        return $passageUrl;
    }

    /**
     * Fetch the webpage content for the specified passage.
     * Example API endpoint:
     * https://4.dbt.io/api/bibles/filesets/:fileset_id/:book/:chapter
     *
     * @return array The webpage content as an array.
     */
    public function getWebpage(): array
    {
        $url = 'bibles/filesets/' . $this->bible->getExternalId();
        $url .= '/' . $this->passageReference->getBookID() . '/';
        $url .= $this->passageReference->getChapterStart();
        $url .= '?verse_start=' . $this->passageReference->getVerseStart();
        $url .= '&verse_end=' . $this->passageReference->getVerseEnd();

        // Instantiate the connection service to fetch the passage.
        $passage = new BibleBrainConnectionService($url);

        // Debugging and returning response data.
        print_r($passage->response->data);
        die();

        return $passage->response->data;
    }

    /**
     * Generate the passage text by iterating through the response data.
     *
     * @return string The formatted passage text with verse numbers.
     */
    public function getPassageText(): string
    {
        $text = '';
        foreach ($this->webpage as $item) {
            // Determine the verse number range.
            if ($item->verse_start == $item->verse_end) {
                $verse_number = $item->verse_start;
            } else {
                $verse_number = $item->verse_start . "-" . $item->verse_end;
            }

            // Format the verse text with paragraph tags and superscript verse numbers.
            $text .= '<p>';
            $text .= '<sup class="versenum">' . $verse_number . '</sup>';
            $text .= $item->verse_text;
            $text .= '</p>';
        }
        return $text;
    }

    /**
     * Get the reference in the local language based on API response data.
     *
     * @return string The local language reference for the passage.
     */
    public function getReferenceLocalLanguage(): string
    {
        if (isset($this->webpage[0]) && isset($this->webpage[0]->book_name_alt)) {
            $book_name = $this->webpage[0]->book_name_alt;

            // Construct the local language reference.
            $referenceLocalLanguage = $book_name . ' ';
            $referenceLocalLanguage .= $this->passageReference->getChapterStart();
            $referenceLocalLanguage .= ':';
            $referenceLocalLanguage .= $this->passageReference->getVerseStart();
            $referenceLocalLanguage .= '-';
            $referenceLocalLanguage .= $this->passageReference->getVerseEnd();
        } else {
            // Fallback for missing data.
            $referenceLocalLanguage = 'Unknown Reference';
        }
        return $referenceLocalLanguage;
    }
}
