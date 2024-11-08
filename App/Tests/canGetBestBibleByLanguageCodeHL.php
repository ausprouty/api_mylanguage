<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Services\Database\DatabaseService;

$databaseService = new DatabaseService();
$code = 'eng00';

$bible = new BibleModel($databaseService);
writeLog('canGetBestBibleByLanguageCodeHL-10', 'Test getBestBibleByLanguageCodeHL');
$bible->getBestBibleByLanguageCodeHL($code);
writeLog('canGetBestBibleByLanguageCodeHL-12', $bible->getVolumeName());
echo ("For eng00 you should see Young's Literal Translation<hr>");
print_r($bible->getVolumeName());