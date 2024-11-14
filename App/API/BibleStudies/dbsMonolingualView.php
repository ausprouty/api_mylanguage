<?php
/* First we will see if we have the view of the study you want.
   If not, we will create it
   Then store it
   Then send you the text you need
*/

use App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\BibleStudy\Monolingual\MonolingualDbsTemplateController as MonolingualDbsTemplateController;

$fileName = MonolingualDbsTemplateController::findFileNameView($lesson, $languageCodeHL1);
$path = MonolingualDbsTemplateController::getPathView();
$filePath = $path . $fileName;
writeLogDebug('dbsFilePath-13', $filePath);
//if (!file_exists($filePath)){
$study = new MonolingualDbsTemplateController($lesson, $languageCodeHL1);
$study->setMonolingualTemplate('monolingualDbsView.twig');
$html =  $study->getTemplate();
$study->saveMonolingualView();
//}
$response = file_get_contents($filePath);
ReturnDataController::returnData($response);
