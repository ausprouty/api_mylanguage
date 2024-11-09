<?php

use App\Controllers\ReturnDataController as ReturnDataController;
use App\Models\Language\LanguageModel as LanguageModel;
use App\Services\Database\DatabaseService;
use App\Repositories\LanguageRepository;

$databaseService = new DatabaseService();
$languageRepository = new LanguageRepository($databaseService);

$language = new LanguageModel($languageRepository);

$languageCodeHL = strip_tags($languageCodeHL);

$data = $language->findOneByLanguageCodeHL( $languageCodeHL);
ReturnDataController::returnData($data);


