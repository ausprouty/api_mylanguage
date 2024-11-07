<?php

use App\Model\Bible\BibleModel as BibleModel;
use App\Model\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;

$bible=new BibleModel();
$bible->selectBibleByBid(1026);

$passage= new BibleReferenceInfoModel();
$passage->setFromPassage('Luke 1:1-80');
$text = new PassageSelectController ($passage, $bible);
print_r ($text->passageText);
//1026-Luke-1-1-80