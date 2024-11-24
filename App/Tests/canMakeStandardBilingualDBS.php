<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceModel as BibleReferenceModel;
use App\Models\BibleStudy\DbsReferenceModel as DbsReferenceModel;
use App\Controllers\BibleStudy\Bilingual\BilingualDbsTemplateController;
use App\Repositories\LanguageRepository;
use App\Repositories\BibleRepository;
use App\Services\QrCodeGeneratorService;
use App\Controllers\BibleStudy\BibleBlockController;
Use App\Services\Database\DatabaseService;
use App\Factories\LanguageModelFactory;

$databaseService = new DatabaseService();
$languageModelFactory = new LanguageModelFactory($databaseService);
$languageRepository = new LanguageRepository($databaseService, $languageModelFactory); // Assume this interacts with a database or API.
$bibleRepository = new BibleRepository($databaseService);       // Fetches Bible data.
$bibleBlockController = new BibleBlockController(); // Manages Bible content blocks
$qrCodeService = new QrCodeGeneratorService();

$templateContoller =  new BilingualDbsTemplateController (
    $languageRepository,
    $bibleRepository,
    $qrCodeService,
    $bibleBlockController
);
$lang1 = 'eng00';
$lang2 = 'frn00';
$lesson = 3;

$templateContoller->setLanguages($lang1, $lang2);
$templateContoller->setLesson($lesson);
echo ('YOu should see a Bilingual Bible study for English and French Lesson 3<hr>');
echo ($dbs->getLesson());
