<?php
use App\Controller\ReturnDataController ;
use App\Configuration\Config;

$previous = $languageCodeHL;
$directory = Config::get('paths.resources.translations') . 'languages/';
$scanned_directory = array_diff(scandir($directory), array('..', '.'));
foreach ($scanned_directory as $dir){
    if ($dir > $previous){     
        ReturnDataController::returnData($dir);
        die;
    }
}
ReturnDataController::returnData('End');
die;