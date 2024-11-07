<?php



use App\Controllers\BibleStudy\LifeStudyController as LifeStudyController;
use App\Controllers\ReturnDataController as ReturnDataController;

$lessons = new LifeStudyController();

if (!isset($languageCodeHL1) || $languageCodeHL1 === null) {
    $data = $lessons->formatWithEnglishTitle();
} else {
    $data = $lessons->formatWithEthnicTitle($languageCodeHL1);
}
ReturnDataController::returnData($data);
