<?php

namespace App\Services\BibleStudy;

use App\Models\Language\LanguageModel;
use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageModel;
use App\Services\TranslationService;

class MonolingualStudyService extends AbstractBibleStudyService
{
    protected $language;

    public function getLanguageInfo(): LanguageModel
    {
        return $this->languageRepository
            ->findOneLanguageByLanguageCodeHL(
                $this->languageCodeHL1
            );
    }

    public function getBibleInfo(): BibleModel
    {
        return $this->bibleRepository
            ->findBestBibleByLanguageCodeHL(
                $this->languageCodeHL1
            );
    }

    public function getPassageModel(): PassageModel
    {
        $result =
            $this->biblePassageService->getPassageModel(
                $this->primaryBible,
                $this->passageReferenceInfo
            );
        return $result;
    }


    public function getTwigTranslationArray(): array
    {
        $data =  $this->translationService->loadTranslation($this->languageCodeHL1, $this->study);
        $data['bible_reference'] = $this->primaryBiblePassage->getReferenceLocalLanguage();
        $data['Bible_Block'] = $this->primaryBiblePassage->getPassageText();
        $data['url'] = $this->primaryBiblePassage->getPassageUrl();
        $description_twig_key = $this->studyReferenceInfo->getDescriptionTwigKey();
        $data['title'] = $data[$description_twig_key] ;
        $data['language'] = $this->primaryLanguage->getName();
        return $data;
    }

    public function getStudyTemplate(string $study, string $format): string
    {
        $template = $this->templateService->getStudyTemplate('monolingual', $study, $format);

        return $template;
    }

    public function assembleOutput(): string
    {
        $translations = array();
        $translations['language1'] = $this->twigTranslation1;
        $text = $this->twigService->renderFromString($this->template,   ['translations' => $translations]);
        print_r($text);
        die;
        return $text;
    }
}
