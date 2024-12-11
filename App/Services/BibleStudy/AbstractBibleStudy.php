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
use App\Configuration\Config;
use Symfony\Component\String\AbstractString;
use App\Services\BibleStudy\TemplateService;
use App\Services\Language\TranslationService;


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
    protected $template;
    protected $twigTranslation1;

    protected $studyReferenceInfo;
    protected $passageReferenceInfo;

    protected $databaseService;
    protected $languageRepository;
    protected $bibleRepository;
    protected $biblePassageService;
    protected $bibleStudyReferenceFactory;
    protected $passageReferenceFactory;
    protected $templateService;
    protected $translationService;
    // get information about the study lesson including title and Bible reference


    abstract function getLanguageInfo(): LanguageModel;
    abstract function getBibleInfo(): BibleModel;
    abstract function getBibleText(): array;
    abstract function getTemplate(string $format): string;
    abstract function getTwigTranslation():string;

    public function __construct(
        DatabaseService $databaseService,
        LanguageRepository $languageRepository,
        BibleRepository $bibleRepository,
        BibleStudyReferenceFactory  $bibleStudyReferenceFactory,
        BiblePassageService   $biblePassageService,
        PassageReferenceFactory $passageReferenceFactory,
        TemplateService  $templateService,
        TranslationService $translationService
    ) {
        $this->databaseService = $databaseService;
        $this->languageRepository = $languageRepository;
        $this->bibleRepository = $bibleRepository;
        $this->bibleStudyReferenceFactory = $bibleStudyReferenceFactory;
        $this->biblePassageService = $biblePassageService;
        $this->passageReferenceFactory = $passageReferenceFactory;
        $this->templateService = $templateService;
    }

    public function generate(
        $study,
        $format,
        $lesson,
        $languageCodeHL1,
        $languageCodeHL2 = null
    ): array {
        $this->initializeParameters($study, $format, $lesson, $languageCodeHL1, $languageCodeHL2);
        $this->loadLanguageAndBibleInfo();
        $this->prepareReferences();
        $this->buildTemplateAndTranslation();
    
        return $this->assembleOutput();
    }
    
    private function initializeParameters(
        $study,
        $format,
        $lesson,
        $languageCodeHL1,
        $languageCodeHL2
    ): void {
        $this->validateParameters($study, $format, $lesson);
    
        $this->study = $study;
        $this->format = $format;
        $this->lesson = $lesson;
        $this->languageCodeHL1 = $languageCodeHL1;
        $this->languageCodeHL2 = $languageCodeHL2;
    }
    
    private function validateParameters($study, $format, $lesson): void {
        if (empty($study) || empty($format) || empty($lesson)) {
            throw new \InvalidArgumentException('Study, format, and lesson must all be provided.');
        }
    }
    
    private function loadLanguageAndBibleInfo(): void {
        try {
            $this->primaryLanguage = $this->getLanguageInfo();
            $this->primaryBible = $this->getBibleInfo();
        } catch (\Exception $e) {
            throw new \RuntimeException('Error loading language or Bible information: ' . $e->getMessage(), 0, $e);
        }
    }
    
    private function prepareReferences(): void {
        try {
            $this->studyReferenceInfo = $this->getStudyReferenceInfo();
            $this->passageReferenceInfo = $this->passageReferenceFactory->createFromStudy($this->studyReferenceInfo);
            $this->primaryBiblePassage = $this->getBibleText();
        } catch (\Exception $e) {
            error_log('Reference preparation failed: ' . $e->getMessage());
            throw $e;
        }
    }
    
    private function buildTemplateAndTranslation(): void {
        try {
            $this->template = $this->getTemplate($this->format);
            $this->twigTranslation1 = $this->getTwigTranslation();
        } catch (\Exception $e) {
            throw new \RuntimeException('Error building template or translation: ' . $e->getMessage(), 0, $e);
        }
    }
    
    private function assembleOutput(): array {
       $output = array( 
            'status' => 'success',
            'data' => [
                'template' => $this->template ?? 'No template available',
                'translation' => $this->twigTranslation1 ?? 'No translation available',
                'language' => $this->primaryLanguage->getCode() ?? 'Unknown language',
                'bible' => $this->primaryBible->getName() ?? 'Unknown Bible',
            ],
       );
       print_r ($output);
       die;
    }
    
