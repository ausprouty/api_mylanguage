<?php
declare(strict_types=1);

namespace App\Models\Video;

use JsonSerializable;
use App\Support\Caster;

final class VideoModel implements JsonSerializable
{
    private const SOURCES = ['arclight', 'vimeo', 'youtube'];

    private int $id = 0;
    private string $title = '';           // <= 100 chars in DB
    private string $verses = '';          // <= 25 chars in DB

    private string $videoSource = 'arclight'; // enum in DB
    private string $videoPrefix = '';         // <= 15 chars, lower
    private string $videoCode   = '-jf';      // <= 100 chars
    private string $videoSegment = '';        // <= 15 chars (raw token)

    private int $startTimeInSeconds = 0; // 0 = start
    private int $stopTimeInSeconds  = 0; // 0 = no end

    // -------- Hydration (canonical/clean only) --------

    /**
     * Populate with already-normalized values.
     * Factories should convert strings, "MM:SS", etc. BEFORE calling this.
     */
    public function populate(array $data): self
    {
        if (\array_key_exists('id', $data)) {
            $this->setId(Caster::toNonNegativeIntOrZeroOrZero($data['id']));
        }
        if (\array_key_exists('title', $data)) {
            $this->setTitle(Caster::toText($data['title']));
        }
        if (\array_key_exists('verses', $data)) {
            $this->setVerses(Caster::toText($data['verses']));
        }
        if (\array_key_exists('videoSource', $data)) {
            $this->setVideoSource(Caster::toLowerText($data['videoSource']));
        }
        if (\array_key_exists('videoPrefix', $data)) {
            $this->setVideoPrefix(Caster::toLowerText($data['videoPrefix']));
        }
        if (\array_key_exists('videoCode', $data)) {
            $this->setVideoCode(Caster::toText($data['videoCode']));
        }
        if (\array_key_exists('videoSegment', $data)) {
            // Accept raw token only (factory strips ?/&/segment=)
            $this->setVideoSegment(Caster::toText($data['videoSegment']));
        }
        if (\array_key_exists('startTimeInSeconds', $data)) {
            $this->setStartTimeInSeconds(
                Caster::toNonNegativeIntOrZeroOrZero($data['startTimeInSeconds'])
            );
        }
        if (\array_key_exists('stopTimeInSeconds', $data)) {
            $this->setStopTimeInSeconds(
                Caster::toNonNegativeIntOrZeroOrZero($data['stopTimeInSeconds'])
            );
        }
        return $this;
    }

    // -------- Projection --------

    public function toArray(): array
    {
        return [
            'id'                  => $this->id,
            'title'               => $this->title,
            'verses'              => $this->verses,
            'videoSource'         => $this->videoSource,
            'videoPrefix'         => $this->videoPrefix,
            'videoCode'           => $this->videoCode,
            'videoSegment'        => $this->videoSegment,
            'startTimeInSeconds'  => $this->startTimeInSeconds,
            'stopTimeInSeconds'   => $this->stopTimeInSeconds,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // -------- Getters --------

    public function getId(): int { return $this->id; }
    public function getTitle(): string { return $this->title; }
    public function getVerses(): string { return $this->verses; }

    public function getVideoSource(): string { return $this->videoSource; }
    public function getVideoPrefix(): string { return $this->videoPrefix; }
    public function getVideoCode(): string { return $this->videoCode; }
    public function getVideoSegment(): string { return $this->videoSegment; }

    public function getStartTimeInSeconds(): int
    { return $this->startTimeInSeconds; }

    public function getStopTimeInSeconds(): int
    { return $this->stopTimeInSeconds; }

    // -------- Setters (normalize/guard at boundary) --------

    public function setId(int $id): void
    {
        $this->id = \max(0, $id);
    }

    public function setTitle(string $title): void
    {
        $this->title = Caster::toText($title);
    }

    public function setVerses(string $verses): void
    {
        $this->verses = Caster::toText($verses);
    }

    public function setVideoSource(string $source): void
    {
        $s = Caster::toLowerText($source);
        $this->videoSource = \in_array($s, self::SOURCES, true) ? $s : 'arclight';
    }

    public function setVideoPrefix(string $prefix): void
    {
        $this->videoPrefix = Caster::toLowerText($prefix);
    }

    public function setVideoCode(string $code): void
    {
        $this->videoCode = Caster::toText($code);
    }

    /**
     * Store the raw token only (e.g., "JESUS-123").
     * Factories should strip leading '?', '&', and "segment=" if present.
     */
    public function setVideoSegment(string $segment): void
    {
        $this->videoSegment = Caster::toText($segment);
    }

    public function setStartTimeInSeconds(int $seconds): void
    {
        $this->startTimeInSeconds = \max(0, $seconds);
    }

    public function setStopTimeInSeconds(int $seconds): void
    {
        $this->stopTimeInSeconds = \max(0, $seconds);
    }
}
