<?php
/* First we will see if we have the pdf of the study you want.
   If not, we will create it
   Then store it
   Then send you the address of the file you can download
*/

use  App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\BibleStudy\Bilingual\BilingualLifeTemplateController as BilingualLifeTemplateController;
use App\Controller\PdfController as PdfController;



$fileName =  BilingualLifeTemplateController::findFileNamePdf($lesson, $languageCodeHL1, $languageCodeHL2);
$path = BilingualLifeTemplateController::getPathPdf();
$filePath = $path . $fileName;
//if (!file_exists($filePath)){
    $study = new BilingualLifeTemplateController($languageCodeHL1, $languageCodeHL2, $lesson);
    $study->setBilingualTemplate('bilingualLifePrinciplesPdf.template.html');
    $html =  $study->getTemplate();
    $styleSheet = 'dbs.css';
    $mpdf = new PdfController($languageCodeHL1, $languageCodeHL2);
    $mpdf->writePdfToComputer($html, $styleSheet, $filePath);
//}
$url = BilingualLifeTemplateController::getUrlPdf();
$response['url'] = $url . $fileName;
$response['name'] = $fileName;
ReturnDataController::returnData($response);
