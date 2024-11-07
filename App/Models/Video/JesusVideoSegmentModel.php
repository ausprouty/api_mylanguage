<?php

namespace App\Models\Video;

use App\Services\Database\DatabaseService;

class JesusVideoSegmentModel
 {
    private $dbService;
    private $id;
    private $title;
    private $verses;
    private $videoSegment;
    private $stopTime;
   

    public function __construct() {
        $this->databaseService = new DatabaseService();
        $this->id = '';
        $this->title = '';
        $this->verses = '';
        $this->videoSegment= '';
        $this->stopTime= '';
    }

}

