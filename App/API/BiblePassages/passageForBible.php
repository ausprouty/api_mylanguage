<?php

use App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\BiblePassage\PassageSelectController as PassageSelectController;
use App\Model\Bible\BibleModel as BibleModel;
use App\Model\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
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


