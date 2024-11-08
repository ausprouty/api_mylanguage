<?php

use App\Controller\BiblePassage\BibleBrain\BibleBrainTextJsonController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Repositories\BibleRepository;


$bibleRepository = new BibleRepository();
echo ("You may be able to modify this so that you can download an entire book (in nice format) and then parse it.  
Look for the URL ion the BibleBrainJson Controller <hr>");
$bible = new BibleModel($bibleRepository);
$bible->selectBibleByBid(6282);
$bibleReferenceInfo = new BibleReferenceInfoModel();
$bibleReferenceInfo->setFromEntry('Luke 1:1-6');
$passage = new BibleBrainTextJsonController($bibleReferenceInfo, $bible);
$passage->getExternal();
echo ("You may be able to modify this so that you can download an entire book (in nice format) and then parse it.  

echo ($passage->getPassageText());