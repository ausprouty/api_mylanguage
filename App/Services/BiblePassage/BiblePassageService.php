<?php

namespace App\Services\BiblePassage;

use App\Factories\PassageFactory;
use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageModel;
use App\Models\Bible\PassageReferenceModel;
use App\Repositories\PassageRepository;
use App\Services\Database\DatabaseService;

class BiblePassageService
{
    private $databaseService;
    private $bible;
    private $passageReference;
    private $passageRepository;
    private $bpid;

    public function __construct(
        DatabaseService $databaseService,
        PassageRepository $passageRepository
    ) {
        $this->databaseService = $databaseService;
        $this->passageRepository = $passageRepository;
    }

    public function getPassage(BibleModel $bible, PassageReferenceModel $passageReference)
    {
        $this->bible = $bible;
        $this->passageReference = $passageReference;
        $this->bpid = $this->bible->getBid() . '-' . $this->passageReference->getPassageID();

        if ($this->inDatabase()) {
            $passage = $this->retrieveStoredData();
        } else {
            $passage = $this->retrieveExternalPassage();
        }
        return $passage->getProperties();
    }

    private function inDatabase()
    {
        return $this->passageRepository->existsById($this->bpid);
    }

    private function retrieveStoredData()
    {
        $data = $this->passageRepository->findStoredById($this->bpid);
        $passage = PassageFactory::createFromData($data);
        $this->updateUsage($passage);

        return $passage;
    }

    private function updateUsage(PassageModel $passage): void
    {
        $passage->setDateLastUsed(date('Y-m-d'));
        $passage->setTimesUsed($passage->getTimesUsed() + 1);
        $this->passageRepository->updatePassageUse($passage);
    }

    private function retrieveExternalPassage()
    {
        $service = $this->getPassageService();
        $service->getWebpage();
        $service->getPassageUrl();
        $service->getPassageText();
        $service->getReferenceLocalLanguage();
        $passage = $service->getPassageModel();
        return $passage;
        
    }

    private function getPassageService(): AbstractBiblePassageService
    {
        switch ($this->bible->getSource()) {
            case 'bible_brain':
                return new BibleBrainPassageService($this->bible, $this->passageReference,  $this->databaseService);
            case 'bible_gateway':
                return new BibleGatewayPassageService($this->bible, $this->passageReference,  $this->databaseService);
            case 'youversion':
                return new YouVersionPassageService($this->bible, $this->passageReference, $this->databaseService);
            case 'word':
                return new BibleWordPassageService($this->bible,$this->passageReference,  $this->databaseService);
            default:
                throw new \InvalidArgumentException("Unsupported source: " . $this->bible->getSource());
        }
    }
}
