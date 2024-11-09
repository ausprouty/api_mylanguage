<?php
namespace App\Models\BibleStudy;

class LifePrincipleReferenceModel {
    private $lesson;
    private $description;
    private $reference;
    private $testament;
    private $question;
    private $videoCode;
    private $videoSegment;
    private $startTime;
    private $endTime;

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

    public function getDescription()
    {
        return $this->description;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getTestament()
    {
        return $this->testament;
    }

    public function getQuestion()
    {
        return $this->question;
    }

    public function getVideoCode()
    {
        return $this->videoCode;
    }

    public function getVideoSegment()
    {
        return $this->videoSegment;
    }

    public function getStartTime()
    {
        return $this->startTime;
    }

    public function getEndTime()
    {
        return $this->endTime;
    }

    // Setters
    public function setLesson($lesson)
    {
        $this->lesson = $lesson;
    }

    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function setTestament($testament)
    {
        $this->testament = $testament;
    }

    public function setQuestion($question)
    {
        $this->question = $question;
    }

    public function setVideoCode($videoCode)
    {
        $this->videoCode = $videoCode;
    }

    public function setVideoSegment($videoSegment)
    {
        $this->videoSegment = $videoSegment;
    }

    public function setStartTime($startTime)
    {
        $this->startTime = $startTime;
    }

    public function setEndTime($endTime)
    {
        $this->endTime = $endTime;
    }
}
?>
