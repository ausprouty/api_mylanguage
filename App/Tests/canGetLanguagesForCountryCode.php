<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainLanguageController as BibleBrainLanguageController;

echo ('This will show you the first 50 languages spoken in Australia according to Bible Brain.  To get all 80 you need to ask for a second page. See logs for clearer picture <hr>');
$countryCode = 'AU';
$languages = new BibleBrainLanguageController();
$languages->getLanguagesFromCountryCode($countryCode);
print_r($languages->response);
