<?php

 use App\Controller\BibleStudy\DbsStudyController as  DbsStudyController;
 use App\Controller\ReturnDataController as ReturnDataController;

$lessons = new DbsStudyController();
if (!isset ($languageCodeHL1)){
    $data = $lessons->formatWithEnglishTitle();
}
else{
    $data = $lessons->formatWithEthnicTitle($languageCodeHL1);
}
ReturnDataController::returnData($data);
