<?php
use App\Controllers\BiblePassage\BibleBrain\BibleBrainBibleController;
use App\Services\Database\DatabaseService;
use App\Services\Bible\BibleUpdateService;
use App\Repositories\LanguageRepository;
use App\Factories\BibleBrainConnectionFactory;
use App\Models\Bible\BibleModel;
use App\Repositories\BibleRepository;

$databaseService = new DatabaseService();
$languageRepository = new LanguageRepository($databaseService);
$bibleRepository = new BibleRepository($databaseService);
$bibleModel = new BibleModel($bibleRepository);
$bibleUpdateService = new BibleUpdateService($databaseService, $bibleModel);
$bibleBrainConnectionFactory = new BibleBrainConnectionFactory();

$bible = new BibleBrainBibleController($bibleUpdateService,
$languageRepository, $bibleBrainConnectionFactory );
$bible->getFormatTypes();
$bible->response;
print_r("you should see an object below with all the format types<hr>");
flush();
print_r( $bible->response);