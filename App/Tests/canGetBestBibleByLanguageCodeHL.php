<?php
use App\Controllers\BibleController;
use App\Repositories\BibleRepository;
use App\Services\Database\DatabaseService;

// Setup services
$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);

// Instantiate controller
$bibleController = new BibleController($bibleRepository);

// Test
$code = 'eng00';
$result = $bibleController->getBestBibleByLanguageCodeHL($code);

// Output
print_r("can get Best Bible by LanguageCode HL<br>");
flush();
print_r("For eng00 you should see New International Version<hr>");
flush();
print_r($result);
