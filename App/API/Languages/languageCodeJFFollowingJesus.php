<?php
use App\Controller\ReturnDataController as ReturnDataController;
use App\Model\Video\VideoModel as VideoModel;

$result = VideoModel::getLanguageCodeJFFollowingJesus($languageCodeHL);
ReturnDataController::returnData($result);