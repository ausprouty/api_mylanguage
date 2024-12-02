<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Repositories\BibleRepository;
use App\Factories\PassageReferenceFactory;
use App\Repositories\PassageReferenceRepository;
use App\Factories\BibleFactory;
use App\Repositories\PassageRepository;
use App\Services\Database\DatabaseService;

use App\Services\BiblePassage\BiblePassageService;

$databaseService = new DatabaseService();

$passageReferenceRepository = new PassageReferenceRepository($databaseService);
$passageFactory = new PassageReferenceFactory($passageReferenceRepository);
$passageReferenceModel = $passageFactory->createFromEntry('Acts 1:1-11');

$bibleRepository = new BibleRepository($databaseService);
$bibleFactory = new BibleFactory($bibleRepository);
$bibleModel = $bibleFactory->createFromBid(1225);


$passageRepository = new PassageRepository($databaseService);
$biblePassageService = new BiblePassageService($databaseService, $passageRepository);

$passage = $biblePassageService->getPassage($bibleModel, $passageReferenceModel, $databaseService);



print_r($passage);
//1026-Luke-1-1-80