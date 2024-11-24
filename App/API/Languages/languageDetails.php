<?php

use App\Controllers\ReturnDataController as ReturnDataController;
use App\Models\Language\LanguageModel as LanguageModel;
use App\Services\Database\DatabaseService;
use App\Repositories\LanguageRepository;
use App\Factories\LanguageModelFactory;

$databaseService = new DatabaseService();
$languageModelFactory = new LanguageModelFactory($databaseService);
$languageRepository = new LanguageRepository($databaseService, $languageModelFactory);

$language = new LanguageModel($languageRepository);

$languageCodeHL = strip_tags($languageCodeHL);

$data = $language->findOneLanguageByLanguageCodeHL($languageCodeHL);
ReturnDataController::returnData($data);
