<?php



use App\Controller\BibleStudy\LifeStudyController as LifeStudyController;
use App\Controller\ReturnDataController as ReturnDataController;

$lessons = new LifeStudyController();

if (!isset($languageCodeHL1) || $languageCodeHL1 === null) {
    $data = $lessons->formatWithEnglishTitle();
} else {
    $data = $lessons->formatWithEthnicTitle($languageCodeHL1);
}
ReturnDataController::returnData($data);
