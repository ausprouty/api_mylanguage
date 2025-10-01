<?php
declare(strict_types=1);

namespace App\Models\Video;

use JsonSerializable;
use App\Interfaces\ArclightVideoInterface;

class JesusVideoSegmentModel implements ArclightVideoInterface, JsonSerializable
{
    private int $id = 0;
    private string $title = '';
    private string $verses = '';

    private string $videoSource = '';
    private string $videoPrefix = '';
    private string $videoCode   = '';
    private string $videoSegment = '';

    /** Times formatted like "HH:MM:SS" (or "MM:SS").  or start*/
    private string $startTime = 'start';
    private string $endTime   = '';   // canonical
    /** @deprecated kept for backward compatibility with older callers */
    private string $stopTime  = '';   // alias of endTime

    // --- Hydration ---
    public function populateFromArray(array $data): self
    {
        // assign with null coalescing, then normalize stopTime/endTime
        $this->id          = (int)($data['id'] ?? $this->id);
        $this->title       = (string)($data['title'] ?? $this->title);
        $this->verses      = (string)($data['verses'] ?? $this->verses);
        $this->videoSource = (string)($data['videoSource'] ?? $this->videoSource);
        $this->videoPrefix = (string)($data['videoPrefix'] ?? $this->videoPrefix);
        $this->videoCode   = (string)($data['videoCode'] ?? $this->videoCode);
        $this->videoSegment= (string)($data['videoSegment'] ?? $this->videoSegment);
        $this->startTime   = isset($data['startTime']) ? (string)$data['startTime'] : $this->startTime;

        // prefer endTime; accept legacy stopTime
        $end = $data['endTime'] ?? $data['stopTime'] ?? $this->endTime;
        $this->setEndTime((string)$end);

        return $this;
    }

    // --- Projection ---
    public function toArray(): array
    {
        return [
            'id'           => $this->id,
            'title'        => $this->title,
            'verses'       => $this->verses,
            'videoSource'  => $this->videoSource,
            'videoPrefix'  => $this->videoPrefix,
            'videoCode'    => $this->videoCode,
            'videoSegment' => $this->videoSegment,
            'startTime'    => $this->startTime,
            'endTime'      => $this->endTime,
            // keep stopTime for consumers that still expect it
            'stopTime'     => $this->stopTime,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // --- Getters ---
    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getVerses(): string { return $this->verses; }

    public function getVideoSource(): string { return $this->videoSource; }
    public function getVideoPrefix(): string { return $this->videoPrefix; }
    public function getVideoCode(): string { return $this->videoCode; }
    public function getVideoSegment(): string { return $this->videoSegment; }

    public function getStartTime(): string { return $this->startTime; }
    public function getEndTime(): string { return $this->endTime; }
    /** @deprecated use getEndTime() */
    public function getStopTime(): string { return $this->stopTime; }

    // --- Setters ---
    public function setId(int $id): void { $this->id = $id; }
    public function setTitle(string $title): void { $this->title = $title; }
    public function setVerses(string $verses): void { $this->verses = $verses; }

    public function setVideoSource(string $videoSource): void { $this->videoSource = $videoSource; }
    public function setVideoPrefix(string $videoPrefix): void { $this->videoPrefix = $videoPrefix; }
    public function setVideoCode(string $videoCode): void { $this->videoCode = $videoCode; }
    public function setVideoSegment(string $videoSegment): void { $this->videoSegment = $videoSegment; }

    public function setStartTime(string $startTime): void { $this->startTime = $startTime; }

    public function setEndTime(string $endTime): void
    {
        $this->endTime = $endTime;
        // maintain alias for backward compatibility
        $this->stopTime = $endTime;
    }

    /** @deprecated use setEndTime() */
    public function setStopTime(string $stopTime): void
    {
        $this->stopTime = $stopTime;
        $this->endTime = $stopTime;
    }
}
