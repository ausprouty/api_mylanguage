<?php

namespace App\Services\BibleStudy;

abstract class AbstractMonoLingualStudy extends AbstractBibleStudy
{
    protected $language;

    public function getLanguageInfo(): void
    {
        $this->primaryLanguage = $this->languageRepository
            ->findOneLanguageByLanguageCodeHL(
                $this->languageCodeHL1
            );

        return;
    }

    public function getBibleInfo(): void
    {
        $this->primaryBible = $this->bibleRepository
            ->findBestBibleByLanguageCodeHL(
                $this->languageCodeHL1
            );
    }
}
