<?php

namespace App\Model\Video;

use App\Model\Data\DatabaseConnectionModel as DatabaseConnectionModel;

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

