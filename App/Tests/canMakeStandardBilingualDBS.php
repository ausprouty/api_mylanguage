<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceModel as BibleReferenceModel;
use App\Models\BibleStudy\DbsReferenceModel as DbsReferenceModel;
use App\Controllers\BibleStudy\Bilingual\BilingualDbsTemplateController as BilingualDbsTemplateController;


echo ('YOu should see a Bilingual Bible study for English and French Lesson 3<hr>');
$lang1 = 'eng00';
$lang2 = 'frn00';
$lesson = 3;


$dbs = new BilingualDbsTemplateController($lang1, $lang2, $lesson);

echo ($dbs->getTemplate());
