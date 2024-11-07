<?php

use App\Controllers\ReturnDataController as ReturnDataController;
use App\Controllers\BiblePassage\PassageSelectController as PassageSelectController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use stdClass as stdClass;

$bid =intval($_POST['bid']);
$entry =strip_tags($_POST['entry']);
$bible = new BibleModel();
$bible->selectBibleByBid($bid);
$bibleReferenceInfo =new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromEntry($entry);

$passage = new PassageSelectController($bibleReferenceInfo, $bible);

$response = new stdClass();
$response->url = $passage->getPassageUrl();
$response->text = $passage->getPassageText();
ReturnDataController::returnData($response);


