<?php

use App\Controllers\BibleStudy\LeadershipStudyController as LeadershipStudyController;
use App\Controllers\ReturnDataController as ReturnDataController;

$lessons = new LeadershipStudyController();
if (!isset ($languageCodeHL1)){
    $data = $lessons->formatWithEnglishTitle();
}
else{
    $data = $lessons->formatWithEthnicTitle($languageCodeHL1);
}
ReturnDataController::returnData($data);
