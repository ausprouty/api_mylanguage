<?php

use App\Models\Language\LanguageModel as LanguageModel;
use App\Services\Database\DatabaseService;
use App\Repositories\LanguageRepository;

$databaseService = new DatabaseService();
$languageRepository = new LanguageRepository($databaseService);

$language = new LanguageModel($languageRepository);

$language->findOneByLanguageCodeHL( 'frn00');
echo ('This should show the ethnic name of French<hr>');
print_r($language->getEthnicName());