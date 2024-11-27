<?php

use App\Controllers\BiblePassage\BibleWordPassageController as BibleWordPassageController;
use App\Controllers\BiblePassage\BibleGateway\BibleGatewayPassageController;
use App\Factories\BibleModelFactory;
use App\Factories\PassageReferenceModelFactory;
use App\Models\Bible\PassageModel;
use App\Repositories\BibleReferenceRepository;
use App\Repositories\BibleRepository;
use App\Repositories\BiblePassageRepository;
use App\Services\Database\DatabaseService;
use App\Services\LoggerService;

// Initialize required services and repositories
$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);
$biblePassageRepository = new BiblePassageRepository($databaseService);

// Pass the repository to the factory
$bibleModelFactory = new BibleModelFactory($bibleRepository);
// Create a BibleModel and fetch a Bible by ID
$bible = $bibleModelFactory->createFromExternalId('al'); //Albanian Bible ID

if (!$bible) {
    // Log the error with a helpful message
    LoggerService::logError("Bible not found for the requested ID: al ");

    // Send an HTTP 404 response code and terminate with an error message
    http_response_code(404);
    die("Sorry, the requested Bible could not be found.");
}
// Create a PassageReferenceModel from the factory
$bibleReferenceRepository = new BibleReferenceRepository($databaseService);
$passageReferenceModelFactory = new PassageReferenceModelFactory($bibleReferenceRepository);

// Create a PassageReferenceModel for the passage Luke 1:1-6
$bibleReference = $passageReferenceModelFactory->createFromEntry('Luke 1:1-6');

// Fetch and save the passage using the BibleGatewayPassageController
$passageController = new BibleWordPassageController(
    $bibleReference,
    $bible,
    $biblePassageRepository
);

// Fetch the passage text
$biblePassage = $passageController->fetchFromServerFile();

// Ensure $biblePassage is a PassageModel object
// Consider adding documentation to describe the properties and methods of this model
if (!$biblePassage instanceof PassageModel) {
    LoggerService::logError("Invalid PassageModel object returned.");
    die("An error occurred while fetching the Bible passage.");
}

// Retrieve and display the passage text
$passageText = $biblePassage->getPassageText();

echo 'You should see the Bible passage for Luke 1:1-6<hr>';
print_r($passageText);
