<?php
namespace App\Services\BibleStudy;




abstract class AbstractMonoLingualStudy extends AbstractBibleStudy {

    protected $language;
    

    public function getLanguageInfo(): void
    {
        $this->primaryLanguage = 
           $this->languageFactory->findOneLanguageByLanguageCodeHL($this->languageCodeHL1);
    
        return;
    }
    public function getBibleInfo():void{
        print_r($this->languageCodeHL1);
        $this->primaryBible = 
            $this->bibleRepository->findBestBibleByLanguageCodeHL($this->languageCodeHL1);
    }
}
