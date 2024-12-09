<?php

namespace App\Models\BibleStudy;

class LeadershipReferenceModel extends BaseStudyReferenceModel
{
    // Shared properties
    protected string $video_code;
    protected int $video_segment;
    protected string $start_time;
    protected string $end_time;

    

    // Shared getters and setters for video-related properties
    public function getVideoCode(): string
    {
        return $this->video_code;
    }

    public function setVideoCode(string $video_code): void
    {
        $this->video_code = $video_code;
    }

    public function getVideoSegment(): int
    {
        return $this->video_segment;
    }

    public function setVideoSegment(int $video_segment): void
    {
        $this->video_segment = $video_segment;
    }

    public function getStartTime(): string
    {
        return $this->start_time;
    }

    public function setStartTime(string $start_time): void
    {
        $this->start_time = $start_time;
    }

    public function getEndTime(): string
    {
        return $this->end_time;
    }

    public function setEndTime(string $end_time): void
    {
        $this->end_time = $end_time;
    }
}
