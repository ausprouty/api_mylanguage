<?php

use App\Controllers\ReturnDataController;
use App\Controllers\BiblePassage\PassageSelectController;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;
use App\Services\Database\DatabaseService;
use stdClass;

$databaseService = new DatabaseService();

$bid = 1259;
$entry = 'Genesis 2:4-25';

$bible = new BibleModel();
$bible->selectBibleByBid($bid);

$bibleReferenceInfo = new BibleReferenceInfoModel($databaseService);
$bibleReferenceInfo->setFromEntry($entry);

$passage = new PassageSelectController($databaseService,$bibleReferenceInfo, $bible);

$response = new stdClass();
$response->url = $passage->getPassageUrl();
$response->text = $passage->getPassageText();

echo $response->text;
