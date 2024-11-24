<?php

use App\Models\Language\LanguageModel as LanguageModel;
use App\Services\Database\DatabaseService;
use App\Repositories\LanguageRepository;
use App\Factories\LanguageModelFactory;

$databaseService = new DatabaseService();
$languageModelFactory = new LanguageModelFactory($databaseService);
$languageRepository = new LanguageRepository($databaseService, $languageModelFactory);

$language = $languageRepository->findOneLanguageByLanguageCodeHL('frn00');
echo ('This should show the ethnic name of French<hr>');
print_r($language->getEthnicName());
