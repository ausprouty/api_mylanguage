<?php

use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\PassageReferenceModel as PassageReferenceModel;
use App\Repositories\BibleRepository;
use App\Services\Database\DatabaseService;

$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);
$bible = new BibleModel($bibleRepository);
$bible->selectBibleByBid(1237);
$bibleReference = new PassageReferenceModel();
$bibleReference->setFromPassage('Luke 1:1-80');

$passageText = new  PassageSelectController($bibleReference, $bible);
print_r($passageText->passageText);
