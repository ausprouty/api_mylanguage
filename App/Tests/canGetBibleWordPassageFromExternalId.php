<?php
use App\Controllers\BiblePassage\BibleWordPassageController as BibleWordPassageController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Services\Database\DatabaseService;
$databaseService = new DatabaseService();

writeLogDebug('canGetBibleWordPassageFromExternalId-8', $externalId);

$bible=new BibleModel($databaseService);
$bible->selectBibleByExternalId($externalId);

$bid = $bible->getBid();
echo ("You should see Bible passage for Genesis 1:1-5 for $bid<hr>");
$bibleReferenceInfo = new BibleReferenceInfoModel($databaseService);
$bibleReferenceInfo->setFromEntry('Genesis 1:1-5');
$passage= new BibleWordPassageController( $bibleReferenceInfo, $bible);
echo ($passage->getPassageText());

