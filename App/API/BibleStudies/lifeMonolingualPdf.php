<?php
/* First we will see if we have the view of the study you want.
   If not, we will create it
   Then store it
   Then send you the text you need
*/

use App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\BibleStudy\Monolingual\MonolingualLifeTemplateController as MonolingualLifeTemplateController;
use App\Controllers\PdfController as PdfController;

$fileName = MonolingualLifeTemplateController::findFileNameView($lesson, $languageCodeHL1);
$path = MonolingualLifeTemplateController::getPathPdf();
$filePath = $path . $fileName;
//if (!file_exists($filePath)){
$study = new MonolingualLifeTemplateController($lesson, $languageCodeHL1);
$study->setMonolingualTemplate('monolingualLifePrinciplesPdf.twig');
$html =  $study->getTemplate();
$styleSheet = 'dbs.css';
$mpdf = new PdfController($languageCodeHL1);
$mpdf->writePdfToComputer($html, $styleSheet, $filePath);
//}
$url =  MonolingualLifeTemplateController::getUrlPdf();
$response['url'] = $url . $fileName;
$response['name'] = $fileName;
ReturnDataController::returnData($response);
