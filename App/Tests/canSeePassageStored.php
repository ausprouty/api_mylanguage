<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\PassageReferenceModel as PassageReferenceModel;
use App\Repositories\BibleReferenceRepository;
use App\Factories\PassageReferenceModelFactory;
use App\Services\Database\DatabaseService;

$databaseService = new DatabaseService();
$bibleRepository = new BibleReferenceRepository($databaseService);


$passageFactory = new PassageReferenceModelFactory($bibleRerferenceRepository);
$passageFactory->setFromPassage('Luke 1:1-80');
$passage = $passageFactory->getProperties();

$bibleReferenceRepository = new BibleReferenceRepository($databaseService);
$bibleFactory = new BibleModelFactory($bibleRepository);
$bibleFactory->setFromBid(1259);
$bibleFactory->getProperites();
print_r($text->passageText);
//1026-Luke-1-1-80