<?php
use  App\Controller\ReturnDataController as ReturnDataController;
use App\Models\Bible\BibleModel as BibleModel;

$data = BibleModel::getAllBiblesByLanguageCodeHL($languageCodeHL);
ReturnDataController::returnData($data);
die;