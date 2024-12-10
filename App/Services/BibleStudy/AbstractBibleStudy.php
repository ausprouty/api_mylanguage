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
    // get information about the study lesson including title and Bible reference


    abstract function getLanguageInfo(): LanguageModel;
    abstract function getBibleInfo(): BibleModel;
    abstract function getBibleText(): array;
    abstract function getTemplate(string $format): string;

    public function __construct(
        DatabaseService $databaseService,
        LanguageRepository $languageRepository,
        BibleRepository $bibleRepository,
        BibleStudyReferenceFactory  $bibleStudyReferenceFactory,
        BiblePassageService   $biblePassageService,
        PassageReferenceFactory $passageReferenceFactory,
        TemplateService  $templateService,
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
        $format, $lesson, $languageCodeHL1, $languageCodeHL2 = null): array
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
        $this->primaryBiblePassage = $this->getBibleText();
        $this->template = $this->getTemplate($format);
        print_r($this->template);
        $array = array(
            'Bob'=> 'done'
        );
        return $array;
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
