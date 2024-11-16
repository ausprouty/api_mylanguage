<?php

use App\Controllers\ReturnDataController;
use App\Controllers\BiblePassage\PassageSelectController;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;
use App\Repositories\BibleRepository;
use App\Services\Database\DatabaseService;
use App\Factories\BibleModelFactory;
use stdClass;

$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);
$bid = intval($_POST['bid']);
$entry = strip_tags($_POST['entry']);

$factory = new BibleModelFactory($bibleRepository );
$bibleModel = $factory->createFromBid($bid);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromEntry($entry);

$passage = new PassageSelectController($bibleReferenceInfo, $bible);

$response = new stdClass();
$response->url = $passage->getPassageUrl();
$response->text = $passage->getPassageText();
ReturnDataController::returnData($response);
