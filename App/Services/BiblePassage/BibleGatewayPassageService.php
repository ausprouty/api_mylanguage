<?php

namespace App\Service\BiblePassage;

use App\Models\Bible\PassageModel;
use App\Models\Bible\PassageReferenceModel;
use App\Models\Bible\BibleModel;
use App\Repositories\BiblePassageRepository;
use App\Services\Web\BibleGatewayConnectionService;
use App\Services\LoggerService;
use App\Configuration\Config;

/**
 * Controller to fetch Bible passages from BibleGateway
 * and save them to the database.
 */
class BibleGatewayPassageService extends AbstractBiblePassageService
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


    /**
     * Fetches a Bible passage from BibleGateway and saves it to the database.
     */
    public function fetchAndSavePassage(): PassageModel
    {
        $referenceShaped = str_replace(
            ' ',
            '%20',
            $this->passageReference->getEntry()
        );

        $passageUrl = '/passage/?search=' .
            $referenceShaped . '&version=' . $this->bible->getExternalId();
        print_r($passageUrl);
        print_r('<br><hr><br>');
        flush();
        $webpage = new BibleGatewayConnectionService($passageUrl);

        $passageModel = new PassageModel();
        if ($webpage->response) {

            $passageModel->setPassageText(
                $this->formatExternal($webpage->response)
            );
            $passageModel->setReferenceLocalLanguage(
                $this->getReferenceLocalLanguage($webpage->response)
            );
            $passageModel->setPassageUrl($passageUrl);

            $this->biblePassageRepository->savePassageRecord($passageModel);
        }
        return $passageModel;
    }

    /**
     * Extracts the reference in the local language from the webpage.
     *
     * @param string $webpage The HTML content of the webpage.
     * @return string The local reference.
     */
    private function getReferenceLocalLanguage(string $webpage): string
    {
        require_once(Config::get('ROOT_LIBRARIES') . '/simplehtmldom_1_9_1/simple_html_dom.php');

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

    /**
     * Formats the external HTML content into cleaned text.
     *
     * @param string $webpage The HTML content of the webpage.
     * @return string The cleaned passage text.
     */
    private function formatExternal(string $webpage): string
    {
        require_once(Config::get('ROOT_LIBRARIES') . '/simplehtmldom_1_9_1/simple_html_dom.php');

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

    /**
     * Removes specific <sup> tags from the HTML content.
     *
     * @param string $htmlContent The HTML content.
     * @return string The cleaned content.
     */
    private function removeSupTags(string $htmlContent): string
    {
        $pattern = '/<sup[^>]*data-fn[^>]*>.*?<\/sup>/is';
        return preg_replace($pattern, '', $htmlContent);
    }

    /**
     * Balances any unclosed or mismatched <div> tags in the HTML content.
     *
     * @param string $html The HTML content.
     * @return string The balanced HTML content.
     */
    private function balanceDivTags(string $html): string
    {

        // Create a DOM object
        $dom = str_get_html($html);

        // If parsing failed, return the original HTML
        if (!$dom) {
            return $html;
        }

        // Get the cleaned and balanced HTML
        $balancedHtml = $dom->save();

        // Clear the memory used by the DOM object
        $dom->clear();
        unset($dom);

        return $balancedHtml;
    }
}
