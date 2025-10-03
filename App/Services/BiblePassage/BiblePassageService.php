<?php

namespace App\Services\BiblePassage;

use App\Factories\PassageFactory;
use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageModel;
use App\Models\Bible\PassageReferenceModel;
use App\Repositories\PassageRepository;
use App\Services\LoggerService;
use App\Services\Database\DatabaseService;
use App\Services\Passage\AbstractBiblePassageService;
use App\Services\Passage\BibleBrainPassageService;
use App\Services\Passage\BibleGatewayPassageService;
use App\Services\Passage\BibleWordPassageService;
use App\Services\Passage\YouVersionPassageService;

/**
 * Service to manage Bible passages. This class checks if a passage exists in the
 * database and, if not, determines the appropriate service to retrieve and store
 * the passage from an external source.
 */
class BiblePassageService
{
    /** @var DatabaseService The database service for interacting with the database. */
    private $databaseService;

    /** @var BibleModel The Bible model instance. */
    private $bible;

    /** @var PassageReferenceModel The passage reference model instance. */
    private $passageReference;

    /** @var PassageRepository The repository for handling passage data. */
    private $passageRepository;

    /** @var string The unique Bible Passage ID (BPID). */
    private $bpid;

    /**
     * Constructor to initialize dependencies.
     *
     * @param DatabaseService $databaseService The database service instance.
     * @param PassageRepository $passageRepository The passage repository instance.
     */
    public function __construct(
        DatabaseService $databaseService,
        PassageRepository $passageRepository
    ) {
        $this->databaseService = $databaseService;
        $this->passageRepository = $passageRepository;
    }

    /**
     * Retrieves a Bible passage. Checks the database first, and if the passage is
     * not found, it uses the appropriate service to fetch and store the passage.
     *
     * @param BibleModel $bible The Bible model instance.
     * @param PassageReferenceModel $passageReference The passage reference model.
     * @return array The properties of the retrieved passage.
     */
    public function getPassage(BibleModel $bible, PassageReferenceModel $passageReference) :PassageModel
    {
        $this->bible = $bible;
        $this->passageReference = $passageReference;

        // Generate the Bible Passage ID (BPID).
        $this->bpid = $this->bible->getBid() . '-' . $this->passageReference->getPassageID();

        // Check if the passage is in the database or fetch it externally.
        if ($this->inDatabase()) {
            $passageModel = $this->retrieveStoredData();
        } else {
            $passageModel = $this->retrieveExternalPassage();
        }

        // Return the passage properties.
        return $passageModel;
    }

    public function getPassageModel(BibleModel $bible, PassageReferenceModel $passageReference) :PassageModel
    {
        $this->bible = $bible;
        $this->passageReference = $passageReference;

        // Generate the Bible Passage ID (BPID).
        $this->bpid = $this->bible->getBid() . '-' . $this->passageReference->getPassageID();

        // Check if the passage is in the database or fetch it externally.
        if ($this->inDatabase()) {
            $passageModel = $this->retrieveStoredData();
        } else {
            $passageModel = $this->retrieveExternalPassage();
        }

        // Return the passage properties.
        return $passageModel;
    }

    /**
     * Checks if the passage exists in the database.
     *
     * @return bool True if the passage exists, false otherwise.
     */
    private function inDatabase()
    {
        return $this->passageRepository->existsById($this->bpid);
    }

    /**
     * Retrieves the passage from the database and updates its usage statistics.
     *
     * @return PassageModel The retrieved passage model.
     */
    private function retrieveStoredData() : PassageModel
    {
        // Fetch the stored data from the database.
        $data = $this->passageRepository->findStoredById($this->bpid);

        if ($data === null) {
            throw new \RuntimeException("Passage not found: {$this->bpid}");
        }

        // Create a PassageModel from the retrieved data.
        $passageModel = PassageFactory::createFromData($data);

        // Update the usage statistics for the passage.
        $this->updateUsage($passageModel);

        return $passageModel;
    }

    /**
     * Updates the usage statistics of a passage.
     *
     * @param PassageModel $passage The passage model to update.
     * @return void
     */
    private function updateUsage(PassageModel $passageModel): void
    {
        $passageModel->setDateLastUsed(date('Y-m-d'));
        $passageModel->setTimesUsed($passageModel->getTimesUsed() + 1);

        // Save the updated usage information to the database.
        $this->passageRepository->updatePassageUse($passageModel);
    }
    /**
     * Retrieves the passage from an external source using the appropriate service.
     */
    private function retrieveExternalPassage(PassageReferenceModel $reference): PassageModel
    {
        $service = $this->getPassageService();
        return $service->createPassageModel($reference);
    }

    /**
     * Determines the appropriate service to use for fetching the passage.
     *
     * @throws \InvalidArgumentException If the source is unsupported.
     */
    private function getPassageService(): AbstractBiblePassageService
    {
        $source = (string)$this->bible->getSource();
        LoggerService::logInfo('BiblePassageService-163', $source);

        // Strategy map instead of a switch
        $map = [
            'bible_brain'  => BibleBrainPassageService::class,
            'bible_gateway'=> BibleGatewayPassageService::class,
            'youversion'   => YouVersionPassageService::class,
            'word'         => BibleWordPassageService::class,
        ];

        $class = $map[$source] ?? null;
        if ($class === null) {
            throw new \InvalidArgumentException("Unsupported source: {$source}");
        }

        // Construct the service with shared dependencies (no reference here).
        // If your concrete services currently require the reference in the constructor,
        // keep passing it — but still ALSO pass $reference to createPassageModel()
        // so we don’t rely on hidden state.
        return new $class($this->bible, $this->databaseService);
        // If needed for BC:
        // return new $class($this->bible, $this->passageReference, $this->databaseService);
    }

   
}
