<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceModel as BibleReferenceModel;
use App\Controllers\BiblePassage\BibleYouVersionPassageController as BibleYouVersionPassageController;
use App\Services\Database\DatabaseService;

// Instantiate the DatabaseService
$databaseService = new DatabaseService();


$bible = new BibleModel($databaseService);
$bible->selectBibleByBid(1766);
$bibleReference = new BibleReferenceModel($databaseService);
$bibleReference->setFromEntry('Luke 1:1-6');
$passage = new BibleYouVersionPassageController($databaseService, $bibleReference, $bible);
$passage->getPassageUrl();
echo ("You should see a link to the passage at Bible.com.<hr>");
echo ($passage->getPassageUrl());
