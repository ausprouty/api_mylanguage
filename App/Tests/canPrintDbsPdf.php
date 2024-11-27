<?php

// Create an instance of the class:

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\PassageReferenceModel as PassageReferenceModel;
use App\Models\BibleStudy\DbsReferenceModel as DbsReferenceModel;
use App\Controllers\BibleStudy\Bilingual\BilingualDbsTemplateController as BilingualDbsTemplateController;
use Vendor\Mpdf\Mpdf as Mpdf;


$lang1 = 'eng00';
$lang2 = 'frn00';
$lesson = 3;


$dbs = new BilingualDbsTemplateController($lang1, $lang2, $lesson);
$html = $dbs->getTemplate();
$filename = $dbs->getPdfName();


try {
    $mpdf = new Mpdf([
        'mode' => 'utf-8',
        'orientation' => 'P'
    ]);
    $mpdf->SetDisplayMode('fullpage');
    // Write some HTML code:
    $mpdf->WriteHTML($html);
    // Output a PDF file directly to the browser
    $mpdf->Output($filename, 'D');
} catch (MpdfException $e) { // Note: safer fully qualified exception name used for catch
    // Process the exception, log, print etc.
    echo $e->getMessage();
}
