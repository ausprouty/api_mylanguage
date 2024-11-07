<?php

namespace App\Controller\BiblePassage\BibleBrain;

use App\Controllers\BiblePassage\BibleBrain\BibleBrainPassageController;

class BibleBrainTextFormatController extends BibleBrainPassageController
{
 
    public function getPassageText()
    {
        return $this->passageText;
    }
    
}