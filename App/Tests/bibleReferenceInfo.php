<?php

use App\Model\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;


$passage = 'John 3:16-40';
$info =new BibleReferenceInfoModel();
$result= $info->setFromPassage($passage);
print_r  ($result);