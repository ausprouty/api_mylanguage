<?php
/* First we will see if we have the view of the study you want.
   If not, we will create it
   Then store it
   Then send you the text you need
*/

use App\Controllers\ReturnDataController as ReturnDataController;
use App\Controllers\BibleStudy\Bilingual\BilingualLifeTemplateController as BilingualLifeTemplateController;
use App\Controllers\Language\DbsLanguageController as DbsLanguageController;

$fileName = DbsLanguageController::bilingualDbsViewFilename(
    $languageCodeHL1,
    $languageCodeHL2,
    $lesson,
    'Principle'
);
$path = BilingualLifeTemplateController::getPathView();
$filePath = $path . $fileName;
//if (!file_exists($filePath)){
$study = new BilingualLifeTemplateController($languageCodeHL1, $languageCodeHL2, $lesson);
$study->setBilingualTemplate('bilingualLifePrinciplesView.twig');
$html =  $study->getTemplate();
$study->saveBilingualView();
//}
$response = file_get_contents($filePath);
ReturnDataController::returnData($response);
