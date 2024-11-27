<?php

namespace App\Services\BiblePassage;

use App\Services\BiblePassage\BibleBrainPassageService;
use App\Services\BiblePassage\BibleGatewayPassageService;
use App\Services\BiblePassage\YouVersionPassageService;
use App\Services\BiblePassage\BibleWordPassageService;
use App\Services\Database\DatabaseService;

use App\Models\BibleModel;
use App\Models\Bible\BibleReferenceModel;

class BiblePassageService
{

    private $databaseService;
  

    public function __construct(
        DatabaseService $databaseService,
        
    ) {
        $this->databaseService = $databaseService;
    }

    public function getPassage( BiblePassageModel $bible, BibleReferenceModel $passage)

    private function checkDatabase()
    {
        $this->passageId = BiblePassageModel::createBiblePassageId($this->bible->getBid(), $this->bibleReference);
        $passage = new BiblePassageModel();
        $passage->findStoredById($this->passageId);

        if ($passage->getReferenceLocalLanguage()) {
            $this->passageText = $passage->getPassageText();
            $this->passageUrl = $passage->getPassageUrl();
            $this->referenceLocalLanguage = $passage->getReferenceLocalLanguage();
        } else {
            $this->retrieveExternalPassage();
        }

        $this->applyTextDirection();
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

        BiblePassageModel::savePassageRecord($this->passageId, $this->referenceLocalLanguage, $this->passageText, $this->passageUrl);
    }

    

}

    
