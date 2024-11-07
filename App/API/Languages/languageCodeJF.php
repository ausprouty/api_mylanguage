<?php

use App\Controllers\ReturnDataController as ReturnDataController;
use App\Model\Video\VideoModel as VideoModel;

$result = VideoModel::getLanguageCodeJF($languageCodeHL);
ReturnDataController::returnData($result);