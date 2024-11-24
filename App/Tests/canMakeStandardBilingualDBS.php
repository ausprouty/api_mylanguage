<?php

use App\Controllers\BibleStudy\Bilingual\BilingualDbsTemplateController;
use App\Controllers\BibleStudy\BibleBlockController;
use App\Factories\BibleStudyReferenceFactory;
use App\Factories\LanguageModelFactory;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceModel;
use App\Models\BibleStudy\DbsReferenceModel;
use App\Repositories\BibleRepository;
use App\Repositories\LanguageRepository;
use App\Services\Database\DatabaseService;
use App\Services\QrCodeGeneratorService;

// Initialize necessary services and repositories
$databaseService = new DatabaseService();
$languageModelFactory = new LanguageModelFactory($databaseService);
$languageRepository = new LanguageRepository($databaseService, $languageModelFactory);
$bibleRepository = new BibleRepository($databaseService);
$bibleBlockController = new BibleBlockController();
$bibleStudyReferenceFactory = new BibleStudyReferenceFactory($databaseService);
$qrCodeService = new QrCodeGeneratorService();

// Initialize the template controller
$templateController = new BilingualDbsTemplateController(
    $bibleBlockController,
    $bibleRepository,
    $bibleStudyReferenceFactory,
    $languageRepository,
    $qrCodeService
);

// Set up languages and lesson
$lang1 = 'eng00';
$lang2 = 'frn00';
$lesson = 3;

$templateController->setLanguages($lang1, $lang2);
$templateController->setLesson($lesson);

// Display the output
echo 'You should see a Bilingual Bible study for English and French Lesson 3<hr>';
echo $templateController->getLesson();
