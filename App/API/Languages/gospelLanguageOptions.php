<?php

use App\Controller\Language\GospelLanguageController as GospelLanguageController;
use App\Controller\ReturnDataController as ReturnDataController;

$languages = new GospelLanguageController();
$options = $languages->getBilingualOptions();
ReturnDataController::returnData($options);