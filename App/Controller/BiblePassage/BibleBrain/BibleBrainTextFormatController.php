<?php

namespace App\Controller\BiblePassage\BibleBrain;

use App\Controller\BiblePassage\BibleBrain\BibleBrainPassageController;

class BibleBrainTextFormatController extends BibleBrainPassageController
{
 
    public function getPassageText()
    {
        return $this->passageText;
    }
    
}