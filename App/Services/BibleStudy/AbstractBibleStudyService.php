<?php

namespace App\Services\BibleStudy;

use App\Configuration\Config;
use App\Factories\BibleStudyReferenceFactory;
use App\Factories\PassageReferenceFactory;
use App\Models\Bible\BibleModel;
use App\Models\Language\LanguageModel;
use App\Models\BibleStudy\DbsReferenceModel;
use App\Models\BibleStudy\LeadershipReferenceModel;
use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Repositories\BibleRepository;
use App\Repositories\LanguageRepository;
use App\Services\BiblePassage\BiblePassageService;
use App\Services\BibleStudy\TemplateService;
use App\Services\Database\DatabaseService;
use App\Services\Language\TranslationService;
use Symfony\Component\String\AbstractString;

/**
 * Abstract class for Bible Study services.
 * Provides a template for retrieving study, language, and Bible information.
 */
abstract class AbstractBibleStudyService
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

    /**
     * Retrieve language information.
     *
     * @return LanguageModel
     */
    abstract function getLanguageInfo(): LanguageModel;

    /**
     * Retrieve Bible information.
     *
     * @return BibleModel
     */
    abstract function getBibleInfo(): BibleModel;

    /**
     * Retrieve Bible text.
     *
     * @return array
     */
    abstract function getBibleText(): array;

    /**
     * Retrieve the template for the study format.
     *
     * @param string $format The desired format.
     * @return string
     */
    abstract function getStudyTemplate(string $study, string $format): string;

    /**
     * Retrieve translation for Twig template.
     *
     * @return string
     */
    abstract function getTwigTranslationArray(): array;

    /**
     * Constructor for dependency injection.
     *
     * @param DatabaseService $databaseService Database service instance.
     * @param LanguageRepository $languageRepository Language repository instance.
     * @param BibleRepository $bibleRepository Bible repository instance.
     * @param BibleStudyReferenceFactory $bibleStudyReferenceFactory Study reference factory.
     * @param BiblePassageService $biblePassageService Bible passage service.
     * @param PassageReferenceFactory $passageReferenceFactory Passage reference factory.
     * @param TemplateService $templateService Template service instance.
     * @param TranslationService $translationService Translation service instance.
     */
    public function __construct(
        DatabaseService $databaseService,
        LanguageRepository $languageRepository,
        BibleRepository $bibleRepository,
        BibleStudyReferenceFactory $bibleStudyReferenceFactory,
        BiblePassageService $biblePassageService,
        PassageReferenceFactory $passageReferenceFactory,
        TemplateService $templateService,
        TranslationService $translationService
    ) {
        $this->databaseService = $databaseService;
        $this->languageRepository = $languageRepository;
        $this->bibleRepository = $bibleRepository;
        $this->bibleStudyReferenceFactory = $bibleStudyReferenceFactory;
        $this->biblePassageService = $biblePassageService;
        $this->passageReferenceFactory = $passageReferenceFactory;
        $this->templateService = $templateService;
        $this->translationService = $translationService;
    }

    /**
     * Generate the study output.
     *
     * @param string $study The study type.
     * @param string $format The output format.
     * @param int $lesson The lesson number.
     * @param string $languageCodeHL1 Primary language code.
     * @param string|null $languageCodeHL2 Secondary language code (optional).
     * @return array The generated study output.
     */
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

    /**
     * Initialize study parameters.
     *
     * @param string $study The study type.
     * @param string $format The output format.
     * @param int $lesson The lesson number.
     * @param string $languageCodeHL1 Primary language code.
     * @param string|null $languageCodeHL2 Secondary language code (optional).
     */
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

    /**
     * Validate the provided parameters.
     *
     * @param string $study The study type.
     * @param string $format The output format.
     * @param int $lesson The lesson number.
     * @throws \InvalidArgumentException If any parameter is invalid.
     */
    private function validateParameters($study, $format, $lesson): void
    {
        if (empty($study) || empty($format) || empty($lesson)) {
            throw new \InvalidArgumentException(
                'Study, format, and lesson must all be provided.'
            );
        }
    }

    /**
     * Load primary language and Bible information.
     *
     * @throws \RuntimeException If language or Bible info cannot be loaded.
     */
    private function loadLanguageAndBibleInfo(): void
    {
        try {
            $this->primaryLanguage = $this->getLanguageInfo();
            $this->primaryBible = $this->getBibleInfo();
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Error loading language or Bible information: ' .
                $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Prepare study and passage references.
     *
     * @throws \Exception If reference preparation fails.
     */
    private function prepareReferences(): void
    {
        try {
            $this->studyReferenceInfo = $this->getStudyReferenceInfo();
            $this->passageReferenceInfo = $this->passageReferenceFactory
                ->createFromStudy($this->studyReferenceInfo);
            $this->primaryBiblePassage = $this->getBibleText();
        } catch (\Exception $e) {
            error_log('Reference preparation failed: ' . $e->getMessage());
            throw $e;
        }
    }

    public function getStudyReferenceInfo():DbsReferenceModel|LifePrincipleReferenceModel|LeadershipReferenceModel{
        return  $this->bibleStudyReferenceFactory->
        createModel( $this->study, $this->lesson);
    }

    /**
     * Build the study template and translation.
     *
     * @throws \RuntimeException If template or translation fails.
     */
    private function buildTemplateAndTranslation(): void
    {
        try {
            $this->template = $this->getStudyTemplate($this->study, $this->format);
            $this->twigTranslation1 = $this->getTwigTranslationArray();
        } catch (\Exception $e) {
            throw new \RuntimeException(
                'Error building template or translation: ' . $e->getMessage(),
                0,
                $e
            );
        }
    }

    /**
     * Assemble the final study output.
     *
     * @return array The assembled output.
     */
    private function assembleOutput(): array
    {
        return [
            'status' => 'success',
            'data' => [
                'template' => $this->template ?? 'No template available',
                'translation' => $this->twigTranslation1 ?? 'No translation available',
                'language' => $this->primaryLanguage->getCode() ?? 'Unknown language',
                'bible' => $this->primaryBible->getName() ?? 'Unknown Bible',
            ],
        ];
    }
}
