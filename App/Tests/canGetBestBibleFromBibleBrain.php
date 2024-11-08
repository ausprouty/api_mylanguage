<?php

use App\Controllers\BiblePassage\BibleBrain\BibleBrainBibleController;
use App\Services\Database\DatabaseService;
$databaseService = new DatabaseService();

$languageCodeIso = 'en';
$bible = new BibleBrainBibleController($databaseService);
$bible->getDefaultBible($languageCodeIso);
echo("You should see stdClass Object ( [en] => stdClass Object ( [audio] => ENGESV [video] => ENGESV ) )<hr>");
print_r($bible->showResponse());