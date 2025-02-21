<?php

namespace App\Models\Video;


use App\Interfaces\ArclightVideoInterface;
use ReflectionClass;

class JesusVideoSegmentModel implements ArclightVideoInterface{

    private int $id;
    private string $title;
    private string $verses;
    private string $videoSource;
    private string $videoPrefix;
    private string $videoCode;
    private string $videoSegment;
    private string $startTime;
    private string $stopTime;

    public function __construct() {

        $this->id = 0;
        $this->title = '';
        $this->verses = '';
        $this->videoSource = '';
        $this->videoPrefix = '';
        $this->videoCode = '';
        $this->videoSegment = '';
        $this->startTime = 0;
        $this->stopTime = '';
    }
     // Method to populate the model
     public function populateFromArray(array $data): void {
        $this->id = $data['id'] ?? 0;
        $this->title = $data['title'] ?? '';
        $this->verses = $data['verses'] ?? '';
        $this->videoSource = $data['videoSource'] ?? '';
        $this->videoPrefix = $data['videoPrefix'] ?? '';
        $this->videoCode = $data['videoCode'] ?? '';
        $this->videoSegment = $data['videoSegment'] ?? '';
        $this->startTime = $data['startTime'] ?? 0;
        $this->stopTime = $data['stopTime'] ?? '';
    }

    // Getters

     /**
     * Returns the video properties as an associative array.
     *
     * @return array
     */
    public function getProperties(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties();
        $propsArray = [];

        foreach ($properties as $property) {
            $property->setAccessible(true); // Allows access to private property
            $propsArray[$property->getName()] = $property->getValue($this);
        }

        return $propsArray;
    }

    public function getId(): int {
        return $this->id;
    }

    public function getTitle(): string {
        return $this->title;
    }

    public function getVerses(): string {
        return $this->verses;
    }

    public function getVideoSource(): string {
        return $this->videoSource;
    }

    public function getVideoPrefix(): string {
        return $this->videoPrefix;
    }

    public function getVideoCode(): string {
        return $this->videoCode;
    }

    public function getVideoSegment(): string {
        return $this->videoSegment;
    }
    public function getStartTime(): string {
        return $this->startTime;
    }
    public function getEndTime(): string {
        return $this->startTime;
    }


    public function getStopTime(): string {
        return $this->stopTime;
    }

    // Setters
    public function setId(int $id): void {
        $this->id = $id;
    }

    public function setTitle(string $title): void {
        $this->title = $title;
    }

    public function setVerses(string $verses): void {
        $this->verses = $verses;
    }

    public function setVideoSource(string $videoSource): void {
        $this->videoSource = $videoSource;
    }

    public function setVideoPrefix(string $videoPrefix): void {
        $this->videoPrefix = $videoPrefix;
    }

    public function setVideoCode(string $videoCode): void {
        $this->videoCode = $videoCode;
    }

    public function setVideoSegment(string $videoSegment): void {
        $this->videoSegment = $videoSegment;
    }
    public function setStartTime(string $startTime): void {
        $this->stopTime = $startTime;
    }

    public function setStopTime(string $stopTime): void {
        $this->stopTime = $stopTime;
    }
}
