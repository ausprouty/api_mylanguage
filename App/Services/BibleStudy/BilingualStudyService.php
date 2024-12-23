<?php

namespace App\Services\BibleStudy;

use App\Models\Bible\BibleModel;
use App\Models\Language\LanguageModel;

class  BilingualStudyService extends AbstractBibleStudyService
{
    protected $secondaryLanguage;
    protected $secondaryBible;
    protected $secondaryBiblePassage;
    protected $twigTranslation2;


    public function getLanguageInfo(): LanguageModel
    {
        $this->primaryLanguage =
            $this->languageRepository->findOneLanguageByLanguageCodeHL(
                $this->languageCodeHL1
            );
        $this->secondaryLanguage =
            $this->languageRepository->findOneLanguageByLanguageCodeHL(
                $this->languageCodeHL2
            );
        return $this->primaryLanguage;
    }

    public function getStudyTemplate(string $study, string $format): string
    {
        $template = $this->templateService->getStudyTemplate('bilingual', $study, $format);

        return $template;
    }
    public function getTwigTranslationArray(): array
    {
        $this->twigTranslation1 =  $this->translationService->loadTranslation($this->languageCodeHL1, $this->study);
        $this->twigTranslation2 =  $this->translationService->loadTranslation($this->languageCodeHL2, $this->study);

        return  $this->twigTranslation1;
    }

    public function getBibleInfo(): BibleModel
    {
        $this->primaryBible =  $this->bibleRepository
            ->findBestBibleByLanguageCodeHL(
                $this->languageCodeHL1
            );
        $this->secondaryBible =  $this->bibleRepository
            ->findBestBibleByLanguageCodeHL(
                $this->languageCodeHL2
            );
        return $this->primaryBible;
    }

    public function getPassageModel(): array
    {
        $this->primaryBiblePassage =
            $this->biblePassageService->getPassage(
                $this->primaryBible,
                $this->passageReferenceInfo
            );
        $this->secondaryBiblePassage =
            $this->biblePassageService->getPassage(
                $this->secondaryBible,
                $this->passageReferenceInfo
            );
        return $this->primaryBiblePassage;
    }

    public function assembleOutput(): string
    {
        $text = $this->twigService->render($this->template, $this->twigTranslation1);
        print_r($text);
        die();
        //return $text;

    }
}
