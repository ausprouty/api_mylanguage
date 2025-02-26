<?php
/* First we will see if we have the view of the study you want.
   If not, we will create it
   Then store it
   Then send you the text you need
*/

use App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\BibleStudy\Monolingual\MonolingualLeadershipTemplateController as MonolingualLeadershipTemplateController;

$fileName = MonolingualLeadershipTemplateController::findFileNameView($lesson, $languageCodeHL1);
$path = MonolingualLeadershipTemplateController::getPathView();
$filePath = $path . $fileName;
//if (!file_exists($filePath)){
$study = new MonolingualLeadershipTemplateController($lesson, $languageCodeHL1);
$study->setMonolingualTemplate('monolingualLeadershipView.twig');
$html =  $study->getTemplate();
$study->saveMonolingualView();
//}
$response = file_get_contents($filePath);
ReturnDataController::returnData($response);
