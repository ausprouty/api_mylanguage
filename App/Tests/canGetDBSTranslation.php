<?php

use App\Services\Language\TranslationService;

echo ('This should show the French translations <hr>');
$languageCodeHL = 'frn00';
$scope = 'dbs';
$translation = new TranslationService($languageCodeHL, $scope);
print_r($translation->getTranslationData());
