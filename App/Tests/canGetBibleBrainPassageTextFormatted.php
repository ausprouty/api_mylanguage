<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainTextPlainController;
use App\Factories\BibleReferenceInfoModelFactory;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;
use App\Repositories\BibleReferenceInfoRepository;
use App\Repositories\BibleRepository;
use App\Services\Bible\BibleBrainPassageService;
use App\Services\Bible\PassageFormatterService;
use App\Services\Database\DatabaseService;

// Initialize required services and repositories
$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);

// Create a BibleModel and fetch a Bible by ID
$bible = new BibleModel($bibleRepository);
$bibleRepository->findBibleByBid(6349);

// Create a BibleReferenceInfoModel and initialize its repository
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfoModelRepository = 
    new BibleReferenceInfoRepository($databaseService);

// Use the factory to create a reference model from an entry
$bibleReferenceInfoModelFactory = 
    new BibleReferenceInfoModelFactory($bibleReferenceInfoModelRepository);
$bibleReferenceInfoModelFactory->createFromEntry('Luke 1:1-6');

// Initialize the BibleBrainPassageService
$bibleBrainPassageService = 
    new BibleBrainPassageService($bible, $bibleReferenceInfo);

// Initialize the PassageFormatterService
$passageFormatterService = new PassageFormatterService();

// Fix: $bibleReferenceRepository was undefined. Assuming it should be initialized.
$bibleReferenceRepository = new BibleReferenceInfoRepository($databaseService);

// Create the controller for fetching passage data
$passageController = new BibleBrainTextPlainController(
    $passageFormatterService,
    $bibleReferenceRepository
);

// Fetch and print passage data
$passageController->fetchPassageData('eng00', 40, 1, 1, 6);
print_r("canGetBibleBrainLanguageDetails<br>");
flush();
print_r("You should see a nicely formatted text below with verse numbers.<hr>");
flush();

// Print the passage text
print_r ($passageController->getPassageText()    );
