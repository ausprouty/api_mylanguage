<?php

namespace App\Services\BiblePassage;

use App\Services\BiblePassage\BibleBrainPassageService;
use App\Services\BiblePassage\BibleGatewayPassageService;
use App\Services\BiblePassage\YouVersionPassageService;
use App\Services\BiblePassage\BibleWordPassageService;
use App\Services\Database\DatabaseService;
use App\Models\Bible\PassageModel;
use App\Factories\PassageFactory;
use App\Repositories\PassageRepository;

use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageReferenceModel;

class BiblePassageService
{

    private $databaseService;
    private $bible;
    private $passageReference;
    private $passageRepository;
    public  $passage;


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
        $passage = $this->checkDatabase();
        return $passage->getProperties();
    }
    private function checkDatabase()
    {
        $bpid = $this->bible->getBid() . '-' . $this->passageReference->getPassageID();
        if ($this->passageRepository->existsById($bpid)) {
            return  $this->passageRepository->findStoredById($bpid);
        } else {
            return $this->retrieveExternalPassage();
        }
    }

    private function retrieveStoredData($data)
    {
        $passageFactory = new PassageFactory();
        $passage =  $passageFactory::createFromData($data);
        $this->updateUseage($passage);
        return $passage;
    }
    private function updateUseage(PassageModel $passage): void
    {
        // Update dateLastUsed to the current timestamp
        $passage->setDateLastUsed(date('Y-m-d'));
        // Increment timesUsed by 1

        $passage->setTimesUsed($passage->getTimesUsed() + 1);
        // Save changes (example: calling a repository or database method)
        $this->passageRepository->updatePassageUse($passage);
    }

    private function retrieveExternalPassage()
    {
        switch ($this->bible->getSource()) {
            case 'bible_brain':
                $passage = new BibleBrainPassageService($this->bibleReference, $this->bible);
                break;
            case 'bible_gateway':
                $passage = new BibleGatewayPassageService($this->bibleReference, $this->bible);
                break;
            case 'youversion':
                $passage = new YouVersionPassageService($this->bibleReference, $this->bible);
                break;
            case 'word':
                $passage = new BibleWordPassageService($this->bibleReference, $this->bible);
                break;
            default:
                $this->setDefaultPassage();
                return;
        }

        $this->passageText = $passage->getPassageText();
        $this->passageUrl = $passage->getPassageUrl();
        $this->referenceLocalLanguage = $passage->getReferenceLocalLanguage();

        PassageModel::savePassageRecord($this->passageId, $this->referenceLocalLanguage, $this->passageText, $this->passageUrl);
    }
}
