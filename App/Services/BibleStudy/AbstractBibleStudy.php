<?php

namespace App\Services\BibleStudy;

use App\Repositories\LanguageRepository;
use App\Repositories\BibleRepository;
use App\Models\Bible\BibleModel;
use App\Services\Database\DatabaseService;
use App\Factories\BibleStudyReferenceFactory;
use App\Models\Language\LanguageModel;
use App\Services\BiblePassage\BiblePassageService;
use App\Factories\PassageReferenceFactory;

abstract class AbstractBibleStudy
{

    protected $study;
    protected $format;
    protected $language;
    protected $lesson;
    protected $languageCodeHL1;
    protected $languageCodeHL2;

    protected $primaryLanguage;
    protected $primaryBible;
    protected $primaryBiblePassage;

    protected $studyReferenceInfo;
    protected $passageReferenceInfo;

    protected $databaseService;
    protected $languageRepository;
    protected $bibleRepository;
    protected $biblePassageService;
    protected $bibleStudyReferenceFactory;
    protected $passageReferenceFactory;
    // get information about the study lesson including title and Bible reference


    abstract function getLanguageInfo(): LanguageModel;
    abstract function getBibleInfo(): BibleModel;

    public function __construct(
        DatabaseService $databaseService,
        LanguageRepository $languageRepository,
        BibleRepository $bibleRepository,
        BibleStudyReferenceFactory  $bibleStudyReferenceFactory,
        BiblePassageService   $biblePassageService,
        PassageReferenceFactory $passageReferenceFactory,
    ) {
        $this->databaseService = $databaseService;
        $this->languageRepository = $languageRepository;
        $this->bibleRepository = $bibleRepository;
        $this->bibleStudyReferenceFactory = $bibleStudyReferenceFactory;
        $this->biblePassageService = $biblePassageService;
        $this->passageReferenceFactory = $passageReferenceFactory;
    }

    public function generate($study, $format, $lesson, $languageCodeHL1, $languageCodeHL2 = null): string
    {
        $this->study = $study;
        $this->format = $format;
        $this->lesson =  $lesson;
        $this->languageCodeHL1 = $languageCodeHL1;
        $this->languageCodeHL2 = $languageCodeHL2;

        $this->primaryLanguage = $this->getLanguageInfo();
        $this->primaryBible = $this->getBibleInfo();
        $this->studyReferenceInfo = 
             $this->getStudyReferenceInfo();
        $this->passageReferenceInfo = 
             $this->passageReferenceFactory->createFromStudy($this->studyReferenceInfo);
        print_r($this->passageReferenceInfo->getProperties() );
        //$this->getBibleText();
        return 'fred';
    }

    /**
     * Retrieves the study reference information.
     *
     * This method returns a model created by the BibleStudyReferenceFactory
     * based on the study and lesson provided. The returned model can be 
     * one of the following:
     * - DbsReferenceModel
     * - LeadershipReferenceModel
     * - LifePrincipleReferenceModel
     *
     *  @return DbsReferenceModel | LeadershipReferenceModel |LifePrincipleReferenceModel
     *  
     */
    public function getStudyReferenceInfo()
    {
        return 
            $this->bibleStudyReferenceFactory
            ->createModel($this->study, $this->lesson);
       
    }

    public function getMetadata(): array
    {
        return [
            'studyType' => $this->study,
            'format' => $this->format,
            'language' => $this->language,
        ];
    }
}
