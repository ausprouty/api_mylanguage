<?php

// Create an instance of the class:

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\PassageReferenceModel as PassageReferenceModel;
use App\Models\BibleStudy\DbsReferenceModel as DbsReferenceModel;
use App\Controllers\BibleStudy\Bilingual\BilingualDbsTemplateController as BilingualDbsTemplateController;
use App\Services\Database\DatabaseService;

$database = new DatabaseService();


$lang1 = 'eng00';
$lang2 = 'frn00';
$lesson = 3;


$dbs = new BilingualDbsTemplateController($lang1, $lang2, $lesson);
$html = $dbs->getTemplate();
$filename = $dbs->getPdfName();
