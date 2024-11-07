<?php

use  App\Controller\ReturnDataController as ReturnDataController;
use App\Model\AskQuestionModel as AskQuestionModel;

$data = AskQuestionModel::getBestSiteByLanguageCodeHL($languageCodeHL);
ReturnDataController::returnData($data);
die;