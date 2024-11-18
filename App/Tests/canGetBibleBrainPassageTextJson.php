<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainTextJsonController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceModel as BibleReferenceModel;
use App\Services\Database\DatabaseService;

$databaseService = new DatabaseService();

$bible = new BibleModel($databaseService);
$bible->selectBibleByBid(4092);
writeLog('canGetBibleBrainPassageTextJson -12', 'bible->languageName');
$bibleReference = new BibleReferenceModel($databaseService);
$bibleReference->setFromEntry('Luke 1:1-6');
writeLog('canGetBibleBrainPassageTextJson -15', 'bibleReference->getBookID(): ' . $bibleReference->getBookID());
$passage = new BibleBrainTextJsonController($databaseService, $bibleReference, $bible);
$passage->getExternal();
echo ("You should see a json object below.  I have no idea how to use it. <hr>");
print_r($passage->getJson());
