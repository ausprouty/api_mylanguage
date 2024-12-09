<?php

namespace App\Models\BibleStudy;

abstract class BaseStudyReferenceModel
{
    // Shared properties
    protected string $video_code;
    protected int $video_segment;
    protected string $start_time;
    protected string $end_time;

    /**
     * Generic Getter
     *
     * @param string $name Property name to retrieve.
     * @return mixed The value of the property.
     * @throws \Exception If the property does not exist.
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \Exception("Property '{$name}' does not exist.");
    }

    /**
     * Generic Setter
     *
     * @param string $name Property name to set.
     * @param mixed $value Value to assign to the property.
     * @throws \Exception If the property does not exist.
     */
    public function __set(string $name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            throw new \Exception("Property '{$name}' does not exist.");
        }
    }

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
