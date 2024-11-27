<?php

namespace App\Services\BiblePassage;

use App\Services\BiblePassage\BibleBrainPassageService;
use App\Services\BiblePassage\BibleGatewayPassageService;
use App\Services\BiblePassage\YouVersionPassageService;
use App\Services\BiblePassage\BibleWordPassageService;
use App\Services\Database\DatabaseService;
use App\Models\PassageModel;
use App\Factories\PassageModelFactory;

use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageReferenceModel;

class BiblePassageService
{

    private $databaseService;
    private $bible;
    private $passageReference;


    public function __construct(
        DatabaseService $databaseService,

    ) {
        $this->databaseService = $databaseService;
    }

    public function getPassage(BibleModel $bible, PassageReferenceModel $passageReference)
    {
        $this->bible = $bible;
        $this->passageReference = $passageReference;
        $this->checkDatabase();
    }

    private function checkDatabase()
    {
        $bpid = $this->bible->getBid() . '-' . $this->passageReference->getPassageID();
        $query = 'SELECT * FROM bible_passages WHERE bpid = :bpid';
        $params = array(':bpid' => $bpid);
        $data = $this->databaseService->fetchRow($query, $params);
        if ($data) {
            $this->retrieveStoredData($data);
        } else {
            $this->retrieveExternalPassage();
        }
    }

    private function retrieveStoredData($data)
    {
        $passageFactory = new PassageModelFactory();
        $passage =  $passageFactory::createFromData($data);
        $this->updateUseage($passage);
        $output = $passage->getProperties();
    }
    private function updateUseage(PassageModel $passage): bool
    {
        // Update dateLastUsed to the current timestamp
        $passage->setDateLastUsed(date('Y-m-d H:i:s'));
        // Increment timesUsed by 1
        $passage->setTimesUsed($passage->getTimesUsed() + 1);

        // Save changes (example: calling a repository or database method)
        return $this->passageRepository->save($passage);
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
