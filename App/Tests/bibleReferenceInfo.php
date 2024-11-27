<?php

use App\Models\Bible\PassageReferenceModel as PassageReferenceModel;
use App\Repositories\BibleReferenceRepository;
use App\Services\Database\DatabaseService;
use App\Factories\PassageReferenceFactory;


$database = new DatabaseService();
$respository = new BibleReferenceRepository($database);
$factory = new PassageReferenceFactory($respository);


$passage = 'John 3:16-40';
$model = $factory->createFromEntry($passage);
$result = $model->getProperties();
print_r("bibleReference Info <br> You should see results for John 3:16-40<br>");
flush();
print_r($result);
