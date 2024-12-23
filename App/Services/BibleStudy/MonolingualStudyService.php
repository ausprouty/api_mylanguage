<?php

namespace App\Services\BibleStudy;

use App\Models\Language\LanguageModel;
use App\Models\Bible\BibleModel;
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

    public function getPassageModel(): array
    {
        $result =
            $this->biblePassageService->getPassage(
                $this->primaryBible,
                $this->passageReferenceInfo
            );
        return $result;
    }


    public function getTwigTranslationArray(): array
    {
        $data =  $this->translationService->loadTranslation($this->languageCodeHL1, $this->study);
        $data['bible_reference'] = $this->primaryBiblePassage['referenceLocalLanguage'];
        $data['Bible_Block'] = $this->primaryBiblePassage['passageText'];
        $data['url'] = $this->primaryBiblePassage['passageUrl'];

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
