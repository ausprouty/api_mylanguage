<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Repositories\BibleRepository;

$bibleRepository = new BibleRepository();
$bible = new BibleModel($bibleRepository);
$bible->selectBibleByBid(1237);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromPassage('Luke 1:1-80');

$passageText= new  PassageSelectController ($bibleReferenceInfo, $bible);
print_r ($passageText->passageText);