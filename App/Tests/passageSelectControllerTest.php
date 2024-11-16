<?php
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Controllers\BiblePassage\PassageSelectController as PassageSelectController;
use App\Repositories\BibleRepository;
use App\Services\Database\DatabaseService;

$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);

$code = 'eng00';
$entry = 'John 3:16-18';
$bibleInfo = new BibleModel($bibleRepository);
$bibleInfo->getBestBibleByLanguageCodeHL($code);
$referenceInfo =new BibleReferenceInfoModel();
$referenceInfo->setFromEntry($entry);
$passage = new PassageSelectController($referenceInfo, $bibleInfo);
echo ('YOu should see the URL and text of John 3:16-18 below<hr>');
print_r($passage->getPassageUrl());
print_r($passage->getPassageText());
