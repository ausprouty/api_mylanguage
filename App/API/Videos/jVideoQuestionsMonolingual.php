<?php

use App\Controller\ReturnDataController as ReturnDataController;
use App\Controller\BibleStudy\Monolingual\MonolingualTemplateTranslationController as MonolingualTemplateTranslationController;

$questions = new MonolingualTemplateTranslationController(
    $templateName = 'monolingualJesusVideoQuestions', 
    $translationFile ='video' , 
    $languageCodeHL, 
);
ReturnDataController::returnData($questions->getTemplate());