<?php

namespace App\Services\BiblePassage;

use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageModel;
use App\Models\Bible\PassageReferenceModel;
use App\Services\Database\DatabaseService;
use App\Factories\PassageFactory;
use App\Repositories\PassageRepository;
use stdClass;

/**
 * AbstractBiblePassageService provides a base structure for handling Bible passages.
 * Subclasses must implement specific methods for retrieving passage data.
 */
abstract class AbstractBiblePassageService
{
    /** @var PassageReferenceModel The passage reference model instance */
    protected $passageReference;

    /** @var BibleModel The Bible model instance */
    protected $bible;

    /** @var DatabaseService The database service for data interaction */
    protected $databaseService;

    /** @var PassageRepository The repository to manage passage records */
    protected $passageRepository;

    /** @var array Webpage content fetched for the passage */
    protected $webpage;

    /** @var string Bible passage identifier */
    protected $bpid;

    /** @var string The text of the Bible passage */
    protected $passageText;

    /** @var string The local language reference for the passage */
    protected $referenceLocalLanguage;

    /** @var string The URL for the Bible passage */
    protected $passageUrl;

    /**
     * Constructor for initializing the BiblePassageService.
     *
     * @param BibleModel $bible The Bible model instance.
     * @param PassageReferenceModel $passageReference The passage reference model instance.
     * @param DatabaseService $databaseService The database service instance.
     */
    public function __construct(
        BibleModel $bible,
        PassageReferenceModel $passageReference,
        DatabaseService $databaseService
    ) {
        $this->passageReference = $passageReference;
        $this->bible = $bible;
        $this->databaseService = $databaseService;

        // Initialize the passage repository using the provided database service.
        $this->passageRepository = new PassageRepository($this->databaseService);
    }

    /**
     * Get the URL for the passage.
     * Subclasses must implement this method.
     *
     * @return string The URL for the passage.
     */
    abstract public function getPassageUrl(): string;

    /**
     * Get the webpage content for the passage.
     * Subclasses must implement this method.
     *
     * @return  array<string,mixed>|string.
     */
    abstract public function getWebPage(): array|string;

    /**
     * Get the text of the Bible passage.
     * Subclasses must implement this method.
     *
     * @return string The text of the passage.
     */
    abstract public function getPassageText(): string;

    /**
     * Get the local language reference for the passage.
     * Subclasses must implement this method.
     *
     * @return string The local language reference.
     */
    abstract public function getReferenceLocalLanguage(): string;

    /**
     * Create and save a passage model based on the implemented methods.
     *
     * @return PassageModel The created passage model.
     */
    public function createPassageModel(): PassageModel
    {
        // Fetch necessary data by calling abstract methods.
        $this->passageUrl = $this->getPassageUrl();
        $this->webpage = $this->getWebpage();
        $this->passageText = $this->getPassageText();
        $this->referenceLocalLanguage = $this->getReferenceLocalLanguage();

        // Generate a Bible Passage ID (BPID) based on the Bible ID and passage reference.
        $bpid = $this->bible->getBid() . '-' . $this->passageReference->getPassageID();

        // Prepare data for creating the PassageModel.
        $data = new stdClass();
        $data->bpid = $bpid;
        $data->dateChecked = date('Y-m-d');
        $data->dateLastUsed = date('Y-m-d');
        $data->passageText = $this->passageText;
        $data->passageUrl = $this->passageUrl;
        $data->referenceLocalLanguage = $this->referenceLocalLanguage;
        $data->timesUsed = 1;

        // Create a PassageModel instance using the factory and save it to the repository if it has data
        $passageModel = PassageFactory::createFromData($data);
        if (!empty($data->passageText) && strlen(trim($data->passageText)) >= 5) {
            $this->passageRepository->savePassageRecord($passageModel);
        }
        

        return $passageModel;
    }
}
