<?php
use App\Controllers\BiblePassage\BibleBrain\BibleBrainBibleController;
use App\Services\Database\DatabaseService;
$databaseService = new DatabaseService();
echo"you should see an object below with all the format types<hr>";
$bible=new BibleBrainBibleController($databaseService );
$bible->getFormatTypes();
$bible->response;
print_r( $bible->response);