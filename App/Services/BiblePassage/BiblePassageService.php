<?php

namespace App\Services\BiblePassage;

use App\Factories\PassageFactory;
use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageModel;
use App\Models\Bible\PassageReferenceModel;
use App\Repositories\PassageRepository;
use App\Services\Database\DatabaseService;
use App\Services\LoggerService;

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
    public function getPassage(BibleModel $bible, PassageReferenceModel $passageReference)
    {
        $this->bible = $bible;
        $this->passageReference = $passageReference;

        // Generate the Bible Passage ID (BPID).
        $this->bpid = $this->bible->getBid() . '-' . $this->passageReference->getPassageID();

        // Check if the passage is in the database or fetch it externally.
        if ($this->inDatabase()) {
            $passage = $this->retrieveStoredData();
        } else {
            $passage = $this->retrieveExternalPassage();
        }

        // Return the passage properties.
        return $passage->getProperties();
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
    private function retrieveStoredData()
    {
        // Fetch the stored data from the database.
        $data = $this->passageRepository->findStoredById($this->bpid);

        // Create a PassageModel from the retrieved data.
        $passage = PassageFactory::createFromData($data);

        // Update the usage statistics for the passage.
        $this->updateUsage($passage);

        return $passage;
    }

    /**
     * Updates the usage statistics of a passage.
     *
     * @param PassageModel $passage The passage model to update.
     * @return void
     */
    private function updateUsage(PassageModel $passage): void
    {
        $passage->setDateLastUsed(date('Y-m-d'));
        $passage->setTimesUsed($passage->getTimesUsed() + 1);

        // Save the updated usage information to the database.
        $this->passageRepository->updatePassageUse($passage);
    }

    /**
     * Retrieves the passage from an external source using the appropriate service.
     *
     * @return PassageModel The retrieved passage model.
     */
    private function retrieveExternalPassage()
    {
        // Determine the correct service based on the Bible source.
        $service = $this->getPassageService();

        // Use the service to create and store the passage model.
        $passage = $service->createPassageModel();

        return $passage;
    }

    /**
     * Determines the appropriate service to use for fetching the passage.
     *
     * @return AbstractBiblePassageService The service instance.
     * @throws \InvalidArgumentException If the source is unsupported.
     */
    private function getPassageService(): AbstractBiblePassageService
    {
        LoggerService::logInfo('BiblePassageService-163',$this->bible->getSource());
        switch ($this->bible->getSource()) {
            case 'bible_brain':
                return new BibleBrainPassageService(
                    $this->bible,
                    $this->passageReference,
                    $this->databaseService
                );

            case 'bible_gateway':
                return new BibleGatewayPassageService(
                    $this->bible,
                    $this->passageReference,
                    $this->databaseService
                );

            case 'youversion':
                return new YouVersionPassageService(
                    $this->bible,
                    $this->passageReference,
                    $this->databaseService
                );

            case 'word':
                return new BibleWordPassageService(
                    $this->bible,
                    $this->passageReference,
                    $this->databaseService
                );

            default:
                throw new \InvalidArgumentException(
                    "Unsupported source: " . $this->bible->getSource()
                );
        }
    }
}
