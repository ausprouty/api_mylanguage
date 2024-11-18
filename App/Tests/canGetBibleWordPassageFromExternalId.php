<?php

use App\Controllers\BiblePassage\BibleWordPassageController as BibleWordPassageController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceModel as BibleReferenceModel;
use App\Services\Database\DatabaseService;

$databaseService = new DatabaseService();

writeLogDebug('canGetBibleWordPassageFromExternalId-8', $externalId);

$bible = new BibleModel($databaseService);
$bible->selectBibleByExternalId($externalId);

$bid = $bible->getBid();
echo ("You should see Bible passage for Genesis 1:1-5 for $bid<hr>");
$bibleReference = new BibleReferenceModel($databaseService);
$bibleReference->setFromEntry('Genesis 1:1-5');
$passage = new BibleWordPassageController($bibleReference, $bible);
echo ($passage->getPassageText());
