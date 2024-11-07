<?php

use App\Controllers\ReturnDataController as ReturnDataController;
use App\Models\Language\LanguageModel as LanguageModel;

$languageCodeHL = strip_tags($languageCodeHL);
$language = new LanguageModel();
$data = $language->findOneByLanguageCodeHL( $languageCodeHL);
ReturnDataController::returnData($data);


