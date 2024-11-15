<?php

namespace App\Models\Video;

use App\Services\Database\DatabaseService;

class JesusVideoSegmentModel
 {
    private $databaseService;
    private $id;
    private $title;
    private $verses;
    private $videoSegment;
    private $stopTime;
   

    public function __construct(DatabaseService $databaseService) {
        $this->databaseService = $databaseService;
        $this->id = '';
        $this->title = '';
        $this->verses = '';
        $this->videoSegment= '';
        $this->stopTime= '';
    }

}

