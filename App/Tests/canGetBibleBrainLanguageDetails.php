<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainLanguageController as BibleBrainLanguageController;
use App\Services\Bible\BibleBrainLanguageService as BibleBrainLanguageService;
use App\Repositories\LanguageRepository as LanguageRepository;
use App\Services\Database\DatabaseService as DatabaseService;
use App\Models\Language\LanguageModel as LanguageModel;
use App\Factories\LanguageFactory as LanguageFactory;



$databaseService = new DatabaseService();

$languageFactory = new LanguageFactory($databaseService);
$languageRepository = new LanguageRepository($databaseService, $languageFactory);
$languageService = new BibleBrainLanguageService($languageRepository, $languageFactory);
//$languageController = new BibleBrainLanguageController($languageRepository, $languageService);
$languageCodeIso = 'spa';
$languageModel = $languageService->fetchLanguageDetails($languageCodeIso);
print_r("canGetBibleBrainLanguageDetails<br>");
flush();
print_r('You should see Spanish below <hr>');
flush();
print_r($languageModel->getProperties());
//$language->updateBibleBrainLanguageDetails();




/*
[id] => 6411 
 [glotto_id] => stan1288 
 [iso] => spa 
 [name] => Spanish 
 [autonym] => Español (Spanish) 
 [bibles] => 19 
 [filesets] => 88 
 [rolv_code] => )
 */
