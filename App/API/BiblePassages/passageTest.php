<?php

use App\Controllers\ReturnDataController;
use App\Controllers\BiblePassage\PassageSelectController;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;
use App\Services\Database\DatabaseService;
use App\Repositories\BibleRepository;

use stdClass;

$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);

$bid = 1259;
$entry = 'Genesis 2:4-25';

$bible = new BibleModel($bibleRepository );
$bible->selectBibleByBid($bid);

$bibleReferenceInfo = new BibleReferenceInfoModel($databaseService);
$bibleReferenceInfo->setFromEntry($entry);

$passage = new PassageSelectController($databaseService,$bibleReferenceInfo, $bible);

$response = new stdClass();
$response->url = $passage->getPassageUrl();
$response->text = $passage->getPassageText();

echo $response->text;
