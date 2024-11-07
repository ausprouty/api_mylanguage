<?php

// what learning opportunities are there?
// returns options
use  App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\ContentAvailableController  as  ContentAvailableController ;

$content = new ContentAvailableController ($languageCodeHL1, $languageCodeHL2);
ReturnDataController::returnData($content->getAllOptions());

