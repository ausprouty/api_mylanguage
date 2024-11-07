<?php

use App\Model\Bible\BibleModel as BibleModel;
use App\Model\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;

$bible = new BibleModel();
$bible->selectBibleByBid(1237);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromPassage('Luke 1:1-80');

$passageText= new  PassageSelectController ($bibleReferenceInfo, $bible);
print_r ($passageText->passageText);