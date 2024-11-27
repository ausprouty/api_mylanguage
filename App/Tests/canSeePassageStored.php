<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Repositories\BibleRepository;
use App\Factories\PassageReferenceModelFactory;
use App\Repositories\PassageReferenceRepository;
use App\Factories\BibleModelFactory;
use App\Services\Database\DatabaseService;

use App\Services\BiblePassage\BiblePassageService;

$databaseService = new DatabaseService();

$passageReferenceRepository = new PassageReferenceRepository($databaseService);
$passageFactory = new PassageReferenceModelFactory($passageReferenceRepository);
$passageReferenceModel = $passageFactory->createFromEntry('Acts 1:3-11');

$bibleRepository = new BibleRepository($databaseService);
$bibleFactory = new BibleModelFactory($bibleRepository);
$bibleModel= $bibleFactory->createFromBid(1259);

$biblePassageService = new BiblePassageService($databaseService);

$passage = $biblePassageService->getPassage($bibleModel, $passageReferenceModel);



print_r($text->passageText);
//1026-Luke-1-1-80