<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainLanguageController as BibleBrainLanguageController;
use App\Services\Database\DatabaseService;

echo ('This will show you the first 50 languages spoken in Australia according to Bible Brain.  To get all 80 you need to ask for a second page. See logs for clearer picture <hr>');
$countryCode = 'AU';
$databaseService = new DatabaseService();
$languages = new BibleBrainLanguageController($databaseService);
$languages->getLanguagesFromCountryCode($countryCode);
print_r($languages->response);
