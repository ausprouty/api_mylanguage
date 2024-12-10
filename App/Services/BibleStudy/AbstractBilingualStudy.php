<?php

namespace App\Services\BibleStudy;
use App\Models\Language\LanguageModel;

abstract class AbstractBiLingualStudy extends AbstractBibleStudy
{
    protected $secondaryLanguage;
    protected $secondaryBible;


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
}
