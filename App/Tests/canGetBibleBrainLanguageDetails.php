<?php

use App\Controller\BiblePassage\BibleBrain\BibleBrainLanguageController as BibleBrainLanguageController;

$languageCodeIso = 'spa';
$language = new BibleBrainLanguageController();
$language->getlanguageDetails($languageCodeIso);
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