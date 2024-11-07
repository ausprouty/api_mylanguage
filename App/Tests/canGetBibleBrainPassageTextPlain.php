<?php
use App\Controllers\BiblePassage\BibleBrain\BibleBrainTextPlainController;
use App\Model\Bible\BibleModel as BibleModel;
use App\Model\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;

echo ("You should see a nicely formatted text below with verse numbers.<hr>");
$bible = new BibleModel();
$bible->selectBibleByBid(1782);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromPassage('Luke 1:1-6');
$passage = new BibleBrainTextPlainController($bibleReferenceInfo, $bible);
$passage->getExternal();
echo ($passage->getPassageText());