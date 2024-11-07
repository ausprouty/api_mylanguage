<?php
/* First we will see if we have the view of the study you want.
   If not, we will create it
   Then store it
   Then send you the text you need
*/
use  App\Controller\ReturnDataController as ReturnDataController;
use  App\Controller\BibleStudy\Monolingual\MonolingualLifeTemplateController as MonolingualLifeTemplateController;

$fileName = MonolingualLifeTemplateController::findFileNameView($lesson, $languageCodeHL1);
$path = MonolingualLifeTemplateController::getPathView();
$filePath = $path . $fileName;
//if (!file_exists($filePath)){
    $study = new MonolingualLifeTemplateController($lesson, $languageCodeHL1);
    $study->setMonolingualTemplate('monolingualLifePrinciplesView.template.html');
    $html =  $study->getTemplate();
    $study->saveMonolingualView();
//}
$response = file_get_contents($filePath);
ReturnDataController::returnData($response);