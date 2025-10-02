<?php
declare(strict_types=1);

namespace App\Services\BibleStudy;

use App\Factories\BibleStudyReferenceFactory;
use App\Factories\PassageReferenceFactory;
use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageModel;
use App\Models\Bible\PassageReferenceModel;
use App\Models\BibleStudy\StudyReferenceModel;
use App\Models\Language\LanguageModel;
use App\Repositories\BibleRepository;
use App\Repositories\LanguageRepository;
use App\Services\BiblePassage\BiblePassageService;
use App\Services\LoggerService;
use InvalidArgumentException;
use Throwable;

final class BiblePassageJsonService
{
    private string $study;
    private int $lesson;
    private string $languageCodeHL;
    private ?LanguageModel $primaryLanguageModel = null;
    private ?BibleModel $primaryBibleModel = null;
    
    /** @var array<string,mixed> */
    private ?StudyReferenceModel $studyReferenceModel = null;

    /** @var array<string,mixed> */
    private ?PassageReferenceModel $passageReferenceModel = null;

    public array $primaryBiblePassagePayload = [];

    public function __construct(
        private BiblePassageService $biblePassageService,
        private BibleRepository $bibleRepository,
        private BibleStudyReferenceFactory $bibleStudyReferenceFactory,
        private LanguageRepository $languageRepository,
        private PassageReferenceFactory $passageReferenceFactory
    ) {}

    /**
     * Build a bible passage block for a lesson.
     *
     * @return array{
     *   passage: array<string,mixed>|PassageModel|null,
     * }
     */
    public function generateBiblePassageJsonBlock(
        string $study,
        int $lesson,
        string $languageCodeHL
    ): array {
        try {
            $this->initialize($study, $lesson, $languageCodeHL);
            $this->loadLanguageAndBible();
            $this->loadPassageRefs();
            $this->loadBibleText();

            return $this->makeBlock();
        } catch (Throwable $e) {
            LoggerService::logException(
                'BiblePassageJsonService: generation failed',
                $e,
                [
                    'study'   => $study,
                    'lesson'  => $lesson,
                    'langHL'  => $languageCodeHL,
                ]
            );

            return [
                'passage'  => null,
            ];
        }
    }

    private function initialize(
        string $study,
        int $lesson,
        string $languageCodeHL
    ): void {
        if ($study === '') {
            throw new InvalidArgumentException('Study must be provided.');
        }
        if ($lesson < 0) {
            throw new InvalidArgumentException('Lesson must be >= 0.');
        }
        if ($languageCodeHL === '') {
            throw new InvalidArgumentException('Language code is required.');
        }

        $this->study = $study;
        $this->lesson = $lesson;
        $this->languageCodeHL = $languageCodeHL;
    }

    private function loadLanguageAndBible(): void
    {
        $this->primaryLanguageModel =
            $this->languageRepository
                 ->findOneLanguageByLanguageCodeHL($this->languageCodeHL);
        LoggerService::logDebug(
            'LanguageModel',
            'state',
            ['model' => $this->primaryLanguageModel->toArray()]
        );

        $this->primaryBibleModel = $this->bibleRepository
            ->findBestBibleByLanguageCodeHL($this->languageCodeHL);
        LoggerService::logDebug(
             'BibleModel',
            'state',
            [$this->primaryBibleModel->toArray()]
        );

        if ($this->primaryBibleModel === null) {
            throw new InvalidArgumentException(
                'No primary Bible found for language ' . $this->languageCodeHL
            );
        }
    }

    private function loadPassageRefs(): void
    {
        // e.g., returns model
        $this->studyReferenceModel = $this->bibleStudyReferenceFactory
            ->createModel($this->study, $this->lesson);

        // Derive concrete passage range(s) for this lesson.
        $this->passageReferenceModel = $this->passageReferenceFactory
            ->createFromStudy($this->studyReferenceModel);
    }

    private function loadBibleText(): void
    {
        $primaryBiblePassagePayload = $this->biblePassageService->getPassage(
             $this->primaryBibleModel,
             $this->passageReferenceModel
        );
    }

    /**
     * @return array{
     *   passage: array<string,mixed>|PassageModel|null,
     * }
     */
    private function makeBlock(): array
    {
        return [
            'passage'  => $this->primaryBiblePassagePayload,
            
        ];
    }
}
