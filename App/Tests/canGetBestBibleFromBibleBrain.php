<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainBibleController;
use App\Services\Database\DatabaseService;
use App\Services\Bible\BibleUpdateService;
use App\Repositories\LanguageRepository;
use App\Factories\BibleBrainConnectionFactory;
use App\Models\Bible\BibleModel;
use App\Repositories\BibleRepository;
use App\Factories\LanguageModelFactory;

$databaseService = new DatabaseService();
$languageModelFactory = new LanguageModelFactory($databaseService);
$languageRepository = new LanguageRepository($databaseService, $languageModelFactory);
$bibleRepository = new BibleRepository($databaseService);
$bibleModel = new BibleModel($bibleRepository);
$bibleUpdateService = new BibleUpdateService($databaseService, $bibleModel);
$bibleBrainConnectionFactory = new BibleBrainConnectionFactory();

$bible = new BibleBrainBibleController($bibleUpdateService,
    $languageRepository, $bibleBrainConnectionFactory);
$languageCodeIso = 'en';
$bible->getDefaultBible($languageCodeIso);
print_r("You should see stdClass Object ( [en] => stdClass Object ( [audio] => ENGESV [video] => ENGESV ) )<hr>");
flush();
print_r($bible->response);