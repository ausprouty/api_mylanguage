<?php

namespace App\Services\BiblePassage;

use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageModel;
use App\Models\Bible\PassageReferenceModel;
use App\Services\Database\DatabaseService;
use App\Factories\PassageFactory;
use App\Repositories\PassageRepository;
use stdClass;

abstract class AbstractBiblePassageService
{
    protected $passageReference;
    protected $bible;
    protected $databaseService;
    protected $passageRepository;
    protected $webpage;
    protected $bpid;
    protected $passageText;
    protected $referenceLocalLanguage;
    protected $passageUrl;

    public function __construct(
     
        BibleModel $bible,
        PassageReferenceModel $passageReference,
        DatabaseService $databaseService
    ) {
        $this->passageReference = $passageReference;
        $this->bible = $bible;
        $this->databaseService = $databaseService;
        $this->passageRepository = new PassageRepository($this->databaseService);
    }

    // Force subclasses to implement these methods
    abstract public function getPassageUrl(): void;
    abstract public function getWebpage(): void;
    abstract public function getPassageText(): void;
    abstract public function getReferenceLocalLanguage(): void;
    
    public function getPassageModel(): PassageModel
    {
        $bpid = $this->bible->getBid() . '-' . $this->passageReference->getPassageID();
        $data = new stdClass();
        $data->bpid = $bpid;
        $data->dateChecked = date('Y-m-d');
        $data->dateLastUsed = date('Y-m-d');
        $data->passageText = $this->passageText;
        $data->passageUrl = $this->passageUrl;
        $data->referenceLocalLanguage = $this->referenceLocalLanguage;
        $data->timesUsed = 1;

        $passageModel = PassageFactory::createFromData($data);
        $this->passageRepository->savePassageRecord($passageModel);
        return $passageModel;

    }
}
