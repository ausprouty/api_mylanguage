<?php

namespace App\Services\BibleStudy;

abstract class AbstractBiLingualStudy extends AbstractBibleStudy
{
    protected $secondaryLanguage;
    protected $secondaryBible;


    public function getLanguageInfo(): void
    {
        $this->primaryLanguage =
            $this->languageFactory->findOneLanguageByLanguageCodeHL(
                $this->languageCodeHL1
            );
        $this->secondaryLanguage =
            $this->languageFactory->findOneLanguageByLanguageCodeHL(
                $this->languageCodeHL2
            );
        return;
    }
}
