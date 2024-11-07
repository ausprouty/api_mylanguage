<?php

use  App\Controller\ReturnDataController as ReturnDataController;
use App\Controllers\Language\DbsLanguageController as DbsLanguageController;

$languages = new DbsLanguageController();
$options = $languages->getOptions();
ReturnDataController::returnData($options);