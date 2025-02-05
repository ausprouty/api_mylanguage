<?php

namespace App\Services\BibleStudy;

//use App\Configuration\Config;
use App\Factories\BibleStudyReferenceFactory;
use App\Factories\PassageReferenceFactory;
//use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageModel;
//use App\Models\Language\LanguageModel;
use App\Repositories\BibleRepository;
use App\Repositories\LanguageRepository;
use App\Services\BiblePassage\BiblePassageService;
use App\Services\Database\DatabaseService;
use App\Services\Language\TranslationService;
use App\Services\LoggerService;

class BiblePassageJsonService
{
    // Properties remain the same
    protected $study;
    protected $language;
    protected $lesson;
    protected $languageCodeHL;
    protected $primaryLanguage;
    protected $primaryBible;
    public $primaryBiblePassage;
    protected $studyReferenceInfo;
    public $passageReferenceInfo;
    protected $translation;

    protected $biblePassageService;
    protected $bibleRepository;
    protected $bibleStudyReferenceFactory;
    protected $databaseService;
    protected $languageRepository;
    protected $loggerService;
    protected $passageReferenceFactory;
    protected $translationService;


    public function __construct(
        BiblePassageService $biblePassageService,
        BibleRepository $bibleRepository,
        BibleStudyReferenceFactory $bibleStudyReferenceFactory,
        DatabaseService $databaseService,
        LanguageRepository $languageRepository,
        LoggerService $loggerService,
        PassageReferenceFactory $passageReferenceFactory,
        TranslationService $translationService,
    ) {
        $this->biblePassageService = $biblePassageService;
        $this->bibleRepository = $bibleRepository;
        $this->bibleStudyReferenceFactory = $bibleStudyReferenceFactory;
        $this->databaseService = $databaseService;
        $this->languageRepository = $languageRepository;
        $this->loggerService = $loggerService;
        $this->passageReferenceFactory = $passageReferenceFactory;
        $this->translationService = $translationService;
    }


    /**
     * Generate the JSON output containing videoBlock and bibleBlock.
     *
     * @param string $study The study type.
     * @param string $format The output format.
     * @param int $lesson The lesson number.
     * @param string $languageCodeHL1 Primary language code.
     * @param string|null $languageCodeHL2 Secondary language code (optional).
     * @return string JSON output containing videoBlock and bibleBlock.
     */
    public function generateBiblePassageJsonBlock(
        $study,
        $lesson,
        $languageCodeHL,
    ): array {
        try {
            $this->initializeParameters($study, $lesson, $languageCodeHL);
            $this->loadLanguageAndBibleInfo();
            $this->loadBibleText();
            $this->loadTemplatesAndTranslation();
            $block = $this->generateBlock();
            return $block;
        } catch (\Exception $e) {
            $this->loggerService->logError('Error generating JSON blocks', $e->getMessage());
            return json_encode(['error' => 'Failed to generate blocks: ' . $e->getMessage()]);
        }
    }

    private function initializeParameters($study, $lesson, $languageCodeHL): void
    {
        if (empty($study) || empty($lesson) || empty($languageCodeHL)) {
            throw new \InvalidArgumentException('Study, format, and lesson must all be provided.');
        }
        $this->study = $study;
        $this->lesson = $lesson;
        $this->languageCodeHL = $languageCodeHL;
    }

    private function loadLanguageAndBibleInfo(): void
    {
        $this->primaryLanguage = $this->languageRepository->findOneLanguageByLanguageCodeHL($this->languageCodeHL);
        $this->primaryBible = $this->bibleRepository->findBestBibleByLanguageCodeHL($this->languageCodeHL);
    }

    private function loadBibleText(): void
    {
        $this->studyReferenceInfo = $this->bibleStudyReferenceFactory->createModel($this->study, $this->lesson);
        $this->passageReferenceInfo = $this->passageReferenceFactory->createFromStudy($this->studyReferenceInfo);
        $this->primaryBiblePassage = $this->biblePassageService->getPassage($this->primaryBible, $this->passageReferenceInfo);
    }

    private function loadTemplatesAndTranslation(): void
    {
        $this->translation = $this->translationService->loadTranslation($this->languageCodeHL, 'bibleStructured');
    }

    private function generateBlock(): array
    {
        return [
            'bibleBlock' => [
                'passage' => $this->primaryBiblePassage,
                'template' => $this->bibleTemplateName ?? 'No template specified',
                'translation' => $this->translation ?? 'No translation available',
            ],
        ];
    }
}
