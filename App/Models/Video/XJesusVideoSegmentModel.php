<?php
declare(strict_types=1);

namespace App\Models\Video;

use JsonSerializable;
use App\Support\Caster;

/**
 * Canonical video segment model (no public constructor).
 * Populate only via populateFromCanonical() or setters.
 */
final class JesusVideoSegment implements JsonSerializable
{
    // Core identity
    private string $videoSource  = '';   // lower
    private string $videoPrefix  = '';   // lower
    private string $videoCode    = '';   // lower

    // Optional segment + times
    private ?string $videoSegment = null; // may be raw token or ?/&...; null if none
    private int $startSec         = 0;    // seconds (0 = start)
    private int $endSec           = 0;    // seconds (0 = no end)

    // Language context
    private ?string $languageCodeJF = null; // lower; required for Arclight URL but optional here
    private ?string $languageCodeHL = null; // lower; optional

    /**
     * Populate from the canonical array:
     * [
     *   videoSource, videoPrefix, videoCode, videoSegment?, start?, end?,
     *   languageCodeJF?, languageCodeHL?
     * ]
     */
    public function populateFromCanonical(array $a): self
    {
        if (\array_key_exists('videoSource', $a)) {
            $this->setVideoSource((string)$a['videoSource']);
        }
        if (\array_key_exists('videoPrefix', $a)) {
            $this->setVideoPrefix((string)$a['videoPrefix']);
        }
        if (\array_key_exists('videoCode', $a)) {
            $this->setVideoCode((string)$a['videoCode']);
        }
        if (\array_key_exists('videoSegment', $a)) {
            $this->setVideoSegment($a['videoSegment'] ?? null);
        }
        if (\array_key_exists('start', $a)) {
            $this->setStart($a['start']);
        }
        if (\array_key_exists('end', $a)) {
            $this->setEnd($a['end']);
        }
        if (\array_key_exists('languageCodeJF', $a)) {
            $this->setLanguageCodeJF($a['languageCodeJF']);
        }
        if (\array_key_exists('languageCodeHL', $a)) {
            $this->setLanguageCodeHL($a['languageCodeHL']);
        }
        return $this;
    }

    // ----- Getters -----
    public function getVideoSource(): string      { return $this->videoSource; }
    public function getVideoPrefix(): string      { return $this->videoPrefix; }
    public function getVideoCode(): string        { return $this->videoCode; }
    public function getVideoSegment(): ?string    { return $this->videoSegment; }
    public function getStartSec(): int            { return $this->startSec; }
    public function getEndSec(): int              { return $this->endSec; }
    public function getLanguageCodeJF(): ?string  { return $this->languageCodeJF; }
    public function getLanguageCodeHL(): ?string  { return $this->languageCodeHL; }

    // ----- Setters (normalize at the boundary) -----
    public function setVideoSource(string $v): void   { $this->videoSource   = Caster::toLowerText($v); }
    public function setVideoPrefix(string $v): void   { $this->videoPrefix   = Caster::toLowerText($v); }
    public function setVideoCode(string $v): void     { $this->videoCode     = Caster::toLowerText($v); }

    public function setVideoSegment(?string $v): void
    {
        $this->videoSegment = Caster::toTextOrNull($v);
    }

    /** Accepts int seconds, "SS","MM:SS","HH:MM:SS","start","",null. */
    public function setStart(int|string|null $v): void
    {
        $this->startSec = self::toSeconds($v);
    }

    /** Accepts int seconds, "SS","MM:SS","HH:MM:SS","",null. */
    public function setEnd(int|string|null $v): void
    {
        $this->endSec = self::toSeconds($v);
    }

    public function setLanguageCodeJF(?string $v): void { $this->languageCodeJF = Caster::toLowerTextOrNull($v); }
    public function setLanguageCodeHL(?string $v): void { $this->languageCodeHL = Caster::toLowerTextOrNull($v); }

    // ----- Helpers -----
    private static function toSeconds(int|string|null $v): int
    {
        if ($v === null) return 0;
        $t = Caster::toTimecodeOrNull($v);
        if ($t === null || $t === '' || $t === '0' || \strtolower($t) === 'start') {
            return 0;
        }
        if (\ctype_digit($t)) return (int)$t;

        $parts = \array_map('intval', \explode(':', $t));
        return match (\count($parts)) {
            2 => \max(0, $parts[0] * 60 + $parts[1]),
            3 => \max(0, $parts[0] * 3600 + $parts[1] * 60 + $parts[2]),
            default => 0,
        };
    }

    // ----- Projection -----
    public function toArray(): array
    {
        return [
            'videoSource'    => $this->videoSource,
            'videoPrefix'    => $this->videoPrefix,
            'videoCode'      => $this->videoCode,
            'videoSegment'   => $this->videoSegment,
            'start'          => $this->startSec,
            'end'            => $this->endSec,
            'languageCodeJF' => $this->languageCodeJF,
            'languageCodeHL' => $this->languageCodeHL,
        ];
    }

    public function jsonSerialize(): array { return $this->toArray(); }
}
