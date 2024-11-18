<?php

use App\Controllers\BiblePassage\BibleGateway\BibleGatewayPassageController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceModel as BibleReferenceModel;
use App\Services\Database\DatabaseService;

$databaseService = new DatabaseService();

$bible = new BibleModel($databaseService);
$bible->selectBibleByBid(1237);
$bibleReference = new BibleReferenceModel($databaseService);
$bibleReference->setFromEntry('Luke 1:1-80');

$passage = new BibleGatewayPassageController($databaseService, $bibleReference, $bible);
$passage->getExternal();
echo ('You should see Bible passage for Luke 1:1-80<hr>');
print_r($passage->getPassageText());
