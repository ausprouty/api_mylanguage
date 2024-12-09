<?php

namespace App\Services\BibleStudy;

abstract class AbstractBiLingualStudy extends AbstractBibleStudy
{
    protected $secondaryLanguage;
    protected $secondaryBible;


    public function getLanguageInfo(): void
    {
        $this->primaryLanguage =
            $this->languageRepository->findOneLanguageByLanguageCodeHL(
                $this->languageCodeHL1
            );
        $this->secondaryLanguage =
            $this->languageRepository->findOneLanguageByLanguageCodeHL(
                $this->languageCodeHL2
            );
        return;
    }
}
