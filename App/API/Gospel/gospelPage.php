<?php

use App\Controller\ReturnDataController as ReturnDataController;

$gospel= new GospelPageController();
$text = $gospel->getBilingualPage($page);
ReturnDataController::returnData($text);