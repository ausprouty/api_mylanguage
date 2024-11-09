<?php
namespace App\Models\BibleStudy;

class DbsReferenceModel {
    private $lesson;
    public $reference;
    public $description;

    public function __construct($lesson = null, $reference = null, $description = null) {
        $this->lesson = $lesson;
        $this->reference = $reference;
        $this->description = $description;
    }

    // Getters
    public function getLesson()
    {
        return $this->lesson;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getDescription()
    {
        return $this->description;
    }

    // Setters
    public function setLesson($lesson)
    {
        $this->lesson = $lesson;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }
}
?>
