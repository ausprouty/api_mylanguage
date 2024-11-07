<?php

use App\Model\Language\LanguageModel as LanguageModel;


echo ('This should show the ethnic name of French<hr>');
$language = new LanguageModel();
$language->findOneByLanguageCodeHL( 'frn00');
print_r($language->getEthnicName());