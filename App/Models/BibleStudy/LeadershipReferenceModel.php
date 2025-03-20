<?php

namespace App\Models\BibleStudy;

class LeadershipReferenceModel extends BaseStudyReferenceModel
{
    // Shared properties
    protected ?string $videoCode;
    protected int $videoSegment;
    protected ?string $startTime;
    protected ?string $endTime;

    public function __construct()
    {
        // Call parent constructor to initialize base properties
        parent::__construct();

        // Initialize new properties for LeadershipReferenceModel
        $this->videoCode = '';        // Default empty string
        $this->videoSegment = 0;      // Default integer value
        $this->startTime = '';        // Default empty string
        $this->endTime = '';          // Default empty string
    }

    // Shared getters and setters for video-related properties
    public function getVideoCode(): string
    {
        return $this->videoCode;
    }

    public function setVideoCode(?string $videoCode): void
    {
        $this->videoCode = $videoCode;
    }

    public function getVideoSegment(): int
    {
        return $this->videoSegment;
    }

    public function setVideoSegment(int $videoSegment): void
    {
        $this->videoSegment = $videoSegment;
    }

    public function getStartTime(): string
    {
        return $this->startTime;
    }

    public function setStartTime(?string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function getEndTime(): string
    {
        return $this->endTime;
    }

    public function setEndTime(?string $endTime): void
    {
        $this->endTime = $endTime;
    }
}
