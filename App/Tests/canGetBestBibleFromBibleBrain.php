<?php

use App\Controller\BiblePassage\BibleBrain\BibleBrainBibleController;

$languageCodeIso = 'en';
$bible = new BibleBrainBibleController();
$bible->getDefaultBible($languageCodeIso);
echo("You should see stdClass Object ( [en] => stdClass Object ( [audio] => ENGESV [video] => ENGESV ) )<hr>");
print_r($bible->showResponse());