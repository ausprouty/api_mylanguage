<?php

use App\Controllers\ReturnDataController as ReturnDataController;
use App\Models\Bible\BibleModel as BibleModel;

$data = BibleModel::getTextBiblesByLanguageCodeHL($languageCodeHL );
ReturnDataController::returnData($data);
die;