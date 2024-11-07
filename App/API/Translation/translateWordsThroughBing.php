<?php

use App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\Language\TranslationController as TranslationController;
$text = 'Please Translate this';
$destinationLanguage = 'de';
$data = TranslationController::TranslateText($text, $destinationLanguage, $sourceLanguage = 'eng');
//ReturnDataController::returnData($data);
echo $data;
echo 'fred';
