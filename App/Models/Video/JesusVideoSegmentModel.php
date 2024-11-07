<?php

namespace App\Models\Video;

use App\Models\Data\DatabaseConnectionModel as DatabaseConnectionModel;

class JesusVideoSegmentModel
 {
    private $dbConnection;
    private $id;
    private $title;
    private $verses;
    private $videoSegment;
    private $stopTime;
   

    public function __construct() {
        $this->dbConnection = new DatabaseConnectionModel();
        $this->id = '';
        $this->title = '';
        $this->verses = '';
        $this->videoSegment= '';
        $this->stopTime= '';
    }

}

