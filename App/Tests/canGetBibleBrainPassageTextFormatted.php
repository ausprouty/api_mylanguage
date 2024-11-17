<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainTextPlainController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Repositories\BibleRepository;
use App\Services\Database\DatabaseService;

$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);
$bible = new BibleModel($bibleRepository);
$bible->selectBibleByBid(6349);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromEntry('Luke 1:1-6');

$passage = new BibleBrainTextPlainController($bibleReferenceInfo, $bible);
$passage->getExternal();
print_r  ("canGetBibleBrainLanguageDetails<br>");
flush();
print_r  ("You should see a nicely formatted text below with verse numbers.<hr>");

flush();
echo ($passage->getPassageText());