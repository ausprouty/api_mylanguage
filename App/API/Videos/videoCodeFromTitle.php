<?php

use App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\Video\VideoController as VideoController;

$videoCode = VideoController::getVideoCodeFromTitle($title, $languageCodeHL);
ReturnDataController::returnData($videoCode);
