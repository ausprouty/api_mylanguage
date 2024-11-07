<?php
use App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\Video\JesusVideoSegmentController as JesusVideoSegmentController;


$segments = new JesusVideoSegmentController($languageCodeJF);

ReturnDataController::returnData($data);
