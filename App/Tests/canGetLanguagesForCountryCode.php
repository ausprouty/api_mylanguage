<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainLanguageController as BibleBrainLanguageController;
use App\Services\Database\DatabaseService;
use App\Repositories\LanguageRepository;
use App\Services\Bible\BibleBrainLanguageService;
use App\Services\Debugging;
use App\Factories\LanguageFactory;

echo ('This will show you the first 50 languages spoken in Australia according to Bible Brain.  To get all 80 you need to ask for a second page. See logs for clearer picture <hr>');
$countryCode = 'AU';
$databaseService = new DatabaseService();
$languageFactory = new LanguageFactory($databaseService);
$languageRepository = new LanguageRepository($databaseService, $languageFactory);
$languageService = new BibleBrainLanguageService($languageRepository, $languageFactory);
$languageController = new BibleBrainLanguageController($languageRepository,  $languageService);
$languages = $languageController->getLanguagesFromCountryCode($countryCode);
print_r($languages);
