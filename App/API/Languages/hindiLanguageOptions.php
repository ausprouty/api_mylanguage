<?php

use App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\Language\HindiLanguageController as HindiLanguageController;

$languages = new HindiLanguageController();
$options = $languages->getLanguageOptions();
ReturnDataController::returnData($options);