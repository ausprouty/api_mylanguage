<?php

use App\Controller\BiblePassage\BibleGateway\BibleGatewayPassageController;
use App\Model\Bible\BibleModel as BibleModel;
use App\Model\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;

$bible=new BibleModel();
$bible->selectBibleByBid(1237);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromEntry('Luke 1:1-80');

$passage= new BibleGatewayPassageController($bibleReferenceInfo, $bible);
$passage->getExternal();
echo ('You should see Bible passage for Luke 1:1-80<hr>');
print_r ($passage->getPassageText());
