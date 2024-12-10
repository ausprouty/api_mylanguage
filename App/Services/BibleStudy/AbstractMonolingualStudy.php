<?php

namespace App\Services\BibleStudy;

use App\Models\Language\LanguageModel;
use App\Models\Bible\BibleModel;
use App\Services\TranslationService;

abstract class AbstractMonoLingualStudy extends AbstractBibleStudy
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

    public function getBibleText():array{
        $result = 
        $this->biblePassageService->getPassage
            ($this->primaryBible, 
            $this->passageReferenceInfo);
        return $result;
       
    }

    public function getTwigTranslation(): string{
        return $this->translationService->
            loadTranslationFile($this->languageCodeHL1, $this->study);
    }
}
