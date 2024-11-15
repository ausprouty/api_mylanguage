<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainLanguageController as BibleBrainLanguageController;
use App\Services\Bible\BibleBrainLanguageService as BibleBrainLanguageService;
use App\Repositories\LanguageRepository as LanguageRepository;
use App\Services\Database\DatabaseService as DatabaseService;


$databaseService = new DatabaseService();
$languageRepository = new LanguageRepository($databaseService);
$languageService = new BibleBrainLanguageService($languageRepository);
//$languageController = new BibleBrainLanguageController($languageRepository, $languageService);
$languageCodeIso = 'spa';
$languageService->fetchLanguageDetails($languageCodeIso);
echo ('You should see Spanish below <hr>');
echo ("$language->name  =  $language->autonym  with ISO $language->iso");
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


 