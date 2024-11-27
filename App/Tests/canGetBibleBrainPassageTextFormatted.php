<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainTextPlainController;
use App\Factories\BibleFactory;
use App\Factories\PassageReferenceFactory;
use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageReferenceModel;
use App\Repositories\BibleReferenceRepository;
use App\Repositories\BibleRepository;
use App\Services\Bible\BibleBrainPassageService;
use App\Services\Bible\PassageFormatterService;
use App\Services\Database\DatabaseService;
use App\Services\LoggerService;

// Initialize required services and repositories
$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);

// Pass the repository to the factory
$bibleFactory = new BibleFactory($bibleRepository);

// Create a BibleModel and fetch a Bible by ID
$bible = $bibleFactory->createFromBid(1778); // Albanian Bible ID

if (!$bible) {
    // Log the error with a helpful message
    LoggerService::logError("Bible not found for the requested ID: 6349");

    // Optionally send an HTTP response code
    http_response_code(404);

    // Return a user-friendly error message and terminate
    die("Sorry, the requested Bible could not be found.");
}

// Create a PassageReferenceModel from the factory
$bibleReferenceRepository = new BibleReferenceRepository($databaseService);
$passageReferenceFactory = new PassageReferenceFactory(
    $bibleReferenceRepository
);
$bibleReference = $passageReferenceFactory->createFromEntry('Luke 1:1-6');

// Initialize the BibleBrainPassageService
$bibleBrainPassageService = new BibleBrainPassageService(
    $bible,
    $bibleReference
);

// Initialize the PassageFormatterService
$passageFormatterService = new PassageFormatterService();

// Create the controller for fetching passage data
$passageController = new BibleBrainTextPlainController(
    $passageFormatterService,
    $bibleReferenceRepository
);

// Fetch and print passage data
$passageController->fetchPassageData($bible, $bibleReference);
print_r("canGetBibleBrainLanguageDetails<br>");
flush();
print_r("You should see a nicely formatted text below with verse numbers.<hr>");
flush();

// Print the passage text
print_r($passageController->getPassageText());
