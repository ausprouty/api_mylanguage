<?php

namespace App\Services\BibleStudy;

use App\Repositories\LanguageRepository;
use App\Repositories\BibleRepository;
use App\Services\Database\DatabaseService;
use App\Factories\BibleStudyReferenceFactory;

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

    protected $studyReferenceInfo;

    protected $databaseService;
    protected $languageRepository;
    protected $bibleRepository;
    protected $bibleStudyReferenceFactory;
    // get information about the study lesson including title and Bible reference


    abstract function getLanguageInfo(): void;
    abstract function getBibleInfo(): void;

    public function __construct(
        DatabaseService $databaseService,
        LanguageRepository $languageRepository,
        BibleRepository $bibleRepository,
        BibleStudyReferenceFactory  $bibleStudyReferenceFactory
    ) {
        $this->databaseService = $databaseService;
        $this->languageRepository = $languageRepository;
        $this->bibleRepository = $bibleRepository;
        $this->bibleStudyReferenceFactory = $bibleStudyReferenceFactory;
    }

    public function generate($study, $format, $lesson, $languageCodeHL1, $languageCodeHL2 = null): string
    {
        $this->study = $study;
        $this->format = $format;
        $this->lesson =  $lesson;
        $this->languageCodeHL1 = $languageCodeHL1;
        $this->languageCodeHL2 = $languageCodeHL2;

        $this->getLanguageInfo();
        $this->getBibleInfo();
        $this->getStudyReferenceInfo();
        return 'fred';
    }

    public function getStudyReferenceInfo()
    {
        print_r($this->study);
        print_r($this->lesson);
        $this->studyReferenceInfo =
            $this->bibleStudyReferenceFactory
            ->createModel($this->study, $this->lesson);
        print_r($this->studyReferenceInfo->getProperties());
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
