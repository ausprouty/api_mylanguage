<?php


use App\Services\Database\DatabaseService;
use App\Repositories\BibleRepository;


$databaseService = new DatabaseService();
$bibleRepository = new BibleRepository($databaseService);

$code = 'eng00';
$result = $bibleRepository->findBestBibleByLanguageCodeHL($code);
print_r  ("For eng00 you should see New International Version<hr>");
flush();
print_r($result);