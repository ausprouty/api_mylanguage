<?php

use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Repositories\BibleReferenceInfoRepository;
use App\Services\Database\DatabaseService;

$passage = 'John 3:16-40';
$database = new DatabaseService();
$respository = new BibleReferenceInfoRepository($database);
$info = new BibleReferenceInfoModel($database, $respository);
$info->setFromEntry($passage);
$result = $info->getPublic();
print_r  ($result);