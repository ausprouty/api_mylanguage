<?php
/* First we will see if we have the view of the study you want.
   If not, we will create it
   Then store it
   Then send you the text you need
*/
use App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\BibleStudy\Monolingual\MonolingualLeadershipTemplateController as MonolingualLeadershipTemplateController;
use App\Controllers\PdfController as PdfController;

$fileName = MonolingualLeadershipTemplateController::findFileNamePdf($lesson, $languageCodeHL1);
$path = MonolingualLeadershipTemplateController::getPathPdf();
$filePath = $path . $fileName;
//if (!file_exists($filePath)){
    $study = new MonolingualLeadershipTemplateController($lesson, $languageCodeHL1);
    $study->setMonolingualTemplate('monolingualLeadershipPdf.template.html');
    $html =  $study->getTemplate();
    $styleSheet = 'dbs.css';
    $mpdf = new PdfController($languageCodeHL1);
    $mpdf->writePdfToComputer($html, $styleSheet, $filePath);
//}
$url =  MonolingualLeadershipTemplateController::getUrlPdf();
$response['url'] = $url . $fileName;
$response['name'] = $fileName;
ReturnDataController::returnData($response);