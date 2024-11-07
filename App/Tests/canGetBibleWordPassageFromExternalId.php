<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;


$bible=new BibleModel();
$bible->selectBibleByExternalId($externalId);
$bid = $bible->getBid();
echo ("You should see Bible passage for Genesis 1:1-5 for $bid<hr>");
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromEntry('Genesis 1:1-5');
$passage= new BibleWordPassageController($bibleReferenceInfo, $bible);
echo ($passage->getPassageText());

