<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainTextJsonController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Services\Database\DatabaseService;

$databaseService = new DatabaseService();

$bible = new BibleModel($databaseService );
$bible->selectBibleByBid(4092);
writeLog('canGetBibleBrainPassageTextJson -12', 'bible->languageName');
$bibleReferenceInfo = new BibleReferenceInfoModel($databaseService);
$bibleReferenceInfo->setFromEntry('Luke 1:1-6');
writeLog('canGetBibleBrainPassageTextJson -15', 'bibleReferenceInfo->getBookID(): ' . $bibleReferenceInfo->getBookID());
$passage = new BibleBrainTextJsonController($databaseService, $bibleReferenceInfo, $bible);
$passage->getExternal();
echo ("You should see a json object below.  I have no idea how to use it. <hr>");
print_r ($passage->getJson());