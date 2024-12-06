<?php

namespace App\Services\BibleStudy;

use App\Factories\LanguageFactory;
use App\Factories\BibleFactory;
use App\Services\Database\DatabaseService;

abstract class AbstractBibleStudy {

    protected $study;
    protected $format;
    protected $language;
    protected $session;
    protected $languageCodeHL1;
    protected $languageCodeHL2;

    protected $primaryLanguage;
    protected $primaryBible;

    protected $databaseService;
    protected $languageFactory;
    protected $bibleFactory;
    // get information about the study lesson including title and Bible reference
    
    abstract function getStudyInfo(): array;
    abstract function getLanguageInfo(): void;
    abstract function getBibleInfo(): void;

    public function __construct(
        DatabaseService $databaseService, 
        LanguageFactory $languageFactory) 
    {
        $this->databaseService = $databaseService;
        $this->languageFactory = $languageFactory;
    }
    
    public function generate($study, $format, $session, $languageCodeHL1, $languageCodeHL2 = null): string
    {
        $this->study = $study;
        $this->format = $format;
        $this->session =  $session;
        $this->languageCodeHL1 = $languageCodeHL1;
        $this->languageCodeHL2 = $languageCodeHL2;
        
        $this->getLanguageInfo();
        $this->getBibleInfo();
        return 'fred';
    }

    



    public function getMetadata(): array {
        return [
            'studyType' => $this->study,
            'format' => $this->format,
            'language' => $this->language,
        ];
    }
}
