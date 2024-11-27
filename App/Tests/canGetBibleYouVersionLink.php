<?php

use App\Factories\BibleFactory;
use App\Factories\PassageReferenceFactory;
use App\Models\Bible\PassageModel;
use App\Repositories\BibleReferenceRepository;
use App\Repositories\BibleRepository;
use App\Repositories\BiblePassageRepository;
use App\Services\Database\DatabaseService;
use App\Services\LoggerService;
use App\Controllers\BiblePassage\BibleYouVersionPassageController;
use App\Services\Bible\YouVersionPassageService;

// Initialize required services and repositories
$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);
$biblePassageRepository = new BiblePassageRepository($databaseService);

// Pass the repository to the factory
$bibleFactory = new BibleFactory($bibleRepository);

// Create a BibleModel and fetch a Bible by ID
$bible = $bibleFactory->createFromBid(1735); // Littafi Mai Tsarki 

if (!$bible) {
    // Log the error with a helpful message
    LoggerService::logError("Bible not found for the requested ID: 1735");

    // Send an HTTP 404 response code and terminate with an error message
    http_response_code(404);
    die("Sorry, the requested Bible could not be found.");
}

// Create a PassageReferenceModel from the factory
$bibleReferenceRepository = new BibleReferenceRepository($databaseService);
$passageReferenceFactory = new PassageReferenceFactory($bibleReferenceRepository);

// Create a PassageReferenceModel for the passage Luke 1:1-6
$bibleReference = $passageReferenceFactory->createFromEntry('Luke 1:1-6');

$youVersionPassageService = new YouVersionPassageService($databaseService, $bibleReference, $bible);
$url = $youVersionPassageService->getPassageUrl();
echo ("You should see a link to the passage at Bible.com for the Littafi Mai Tsarki  version of Luke 1:1-6<hr>");
$output = '<a href="' . $url . '">Link to Luke 1:1-6</a>';
echo ($url);
echo ('<br>' . $output);
