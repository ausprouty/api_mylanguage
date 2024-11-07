<?php

use App\Controller\ReturnDataController as ReturnDataController;
use App\Model\Video\VideoModel as VideoModel;

$result = VideoModel::getLanguageCodeJF($languageCodeHL);
ReturnDataController::returnData($result);