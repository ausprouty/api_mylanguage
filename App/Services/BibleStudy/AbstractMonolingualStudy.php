<?php

namespace App\Services\BibleStudy;

use App\Models\Language\LanguageModel;
use App\Models\Bible\BibleModel;

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
}
