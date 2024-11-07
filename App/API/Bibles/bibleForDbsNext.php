<?php
use  App\Controller\ReturnDataController ;

$previous = $languageCodeHL;
$directory = ROOT_TRANSLATIONS . 'languages/';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
foreach ($scanned_directory as $dir){
    if ($dir > $previous){     
        ReturnDataController::returnData($dir);
        die;
    }
}
ReturnDataController::returnData('End');
die;