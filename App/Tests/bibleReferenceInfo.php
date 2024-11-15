<?php

use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Repositories\BibleReferenceInfoRepository;
use App\Services\Database\DatabaseService;
use App\Factories\BibleReferenceInfoModelFactory;


$database = new DatabaseService();
$respository = new BibleReferenceInfoRepository($database);
$factory = new BibleReferenceInfoModelFactory($respository);


$passage = 'John 3:16-40';   
$model = $factory->createFromEntry($passage);
$result = $model->getProperties();
print_r  ($result);