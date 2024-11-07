<?php

use App\Models\BibleStudy\DbsReferenceModel as DbsReferenceModel;

$references = new DbsReferenceModel();
$output =  $references->findByHL('eng00');
print_r($output);