<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainTextPlainController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Repositories\BibleRepository;


$bibleRepository = new BibleRepository();
echo ("You should see a nicely formatted text below with verse numbers.<hr>");
$bible = new BibleModel($bibleRepository);
$bible->selectBibleByBid(6349);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromEntry('Luke 1:1-6');

$passage = new BibleBrainTextPlainController($bibleReferenceInfo, $bible);
$passage->getExternal();
echo ($passage->getPassageText());