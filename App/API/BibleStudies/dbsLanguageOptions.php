<?php

use  App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\Language\DbsLanguageController as DbsLanguageController;

$languages = new DbsLanguageController();
$options = $languages->getOptions();
ReturnDataController::returnData($options);