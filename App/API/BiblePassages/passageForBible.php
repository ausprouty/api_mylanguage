<?php

use App\Controllers\ReturnDataController;
use App\Controllers\BiblePassage\PassageSelectController;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;
use stdClass;

$bid = intval($_POST['bid']);
$entry = strip_tags($_POST['entry']);
$bible = new BibleModel();
$bible->selectBibleByBid($bid);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromEntry($entry);

$passage = new PassageSelectController($bibleReferenceInfo, $bible);

$response = new stdClass();
$response->url = $passage->getPassageUrl();
$response->text = $passage->getPassageText();
ReturnDataController::returnData($response);
