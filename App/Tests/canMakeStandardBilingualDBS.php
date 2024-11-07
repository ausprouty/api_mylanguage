<?php
use App\Model\Bible\BibleModel as BibleModel;
use App\Model\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Model\BibleStudy\DbsReferenceModel as DbsReferenceModel;
use App\Controllers\BibleStudy\Bilingual\BilingualDbsTemplateController as BilingualDbsTemplateController;


echo ('YOu should see a Bilingual Bible study for English and French Lesson 3<hr>');
$lang1 ='eng00';
$lang2= 'frn00';
$lesson = 3;


$dbs = new BilingualDbsTemplateController($lang1, $lang2, $lesson);

echo ($dbs->getTemplate());
