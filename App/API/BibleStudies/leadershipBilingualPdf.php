<?php
/* First we will see if we have the pdf of the study you want.
   If not, we will create it
   Then store it
   Then send you the address of the file you can download
*/
use  App\Controller\ReturnDataController as ReturnDataController;
use App\Controllers\BibleStudy\Bilingual\BilingualLeadershipTemplateController as BilingualLeadershipTemplateController;
use App\Controllers\PdfController as PdfController;


$fileName =  BilingualLeadershipTemplateController::findFileNamePdf($lesson, $languageCodeHL1, $languageCodeHL2);
$path = BilingualLeadershipTemplateController::getPathPdf();
$filePath = $path . $fileName;
//TODO: eliminate commented
//if (!file_exists($filePath)){
    $study = new BilingualLeadershipTemplateController($languageCodeHL1, $languageCodeHL2, $lesson);
    $study->setBilingualTemplate('bilingualLeadershipPdf.template.html');
    $html =  $study->getTemplate();
    $styleSheet = 'dbs.css';
    $mpdf = new PdfController($languageCodeHL1, $languageCodeHL2);
    $mpdf->writePdfToComputer($html, $styleSheet, $filePath);
//}
$url = BilingualLeadershipTemplateController::getUrlPdf();
$response['url'] = $url . $fileName;
$response['name'] = $fileName;
ReturnDataController::returnData($response);
