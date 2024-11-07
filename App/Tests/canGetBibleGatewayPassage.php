<?php

use App\Controllers\BiblePassage\BibleGateway\BibleGatewayPassageController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;

$bible=new BibleModel();
$bible->selectBibleByBid(1237);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromEntry('Luke 1:1-80');

$passage= new BibleGatewayPassageController($bibleReferenceInfo, $bible);
$passage->getExternal();
echo ('You should see Bible passage for Luke 1:1-80<hr>');
print_r ($passage->getPassageText());
