<?php

use App\Model\Bible\BibleModel as BibleModel;

$code = 'eng00';
echo ("For eng00 you should see Young's Literal Translation<hr>");
$bible = new BibleModel();
$bible->getBestBibleByLanguageCodeHL($code);
print_r($bible->getVolumeName());