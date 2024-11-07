<?php

use App\Controller\BiblePassage\BibleBrain\BibleBrainTextJsonController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;

$bible = new BibleModel();
$bible->selectBibleByBid(4092);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromEntry('Luke 1:1-6');
$passage = new BibleBrainTextJsonController($bibleReferenceInfo, $bible);
$passage->getExternal();
echo ("You should see a json object below.  I have no idea how to use it. <hr>");
print_r ($passage->getJson());