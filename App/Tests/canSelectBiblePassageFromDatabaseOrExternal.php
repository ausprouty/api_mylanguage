<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Repositories\BibleRepository;
use App\Services\Database\DatabaseService;

$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);
$bible = new BibleModel($bibleRepository);
$bible->selectBibleByBid(1237);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromPassage('Luke 1:1-80');

$passageText= new  PassageSelectController ($bibleReferenceInfo, $bible);
print_r ($passageText->passageText);