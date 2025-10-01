<?php
declare(strict_types=1);

namespace App\Models\Video;

use JsonSerializable;
use App\Configuration\Config;
use App\Models\Bible\PassageReferenceModel;

class VideoModel implements JsonSerializable
{
    private ?string $videoSource    = null;
    private ?string $videoPrefix    = null;
    private ?string $videoCode      = null;
    private ?string $videoSegment   = null; // e.g. '?segment=JESUS-123' or '&segment=...'
    private int     $startTime      = 0;    // seconds
    private int     $endTime        = 0;    // seconds
    private ?string $arclightUrl    = null;

    private ?string $languageCodeHL = null; // optional (for logging/back-compat)
    private ?string $languageCodeJF = null; // required for Arclight

    public function __construct(array $data = [])
    {
        $this->videoSource    = $data['videoSource']    ?? null;
        $this->videoPrefix    = $data['videoPrefix']    ?? null;
        $this->videoCode      = $data['videoCode']      ?? null;
        $this->videoSegment   = $data['videoSegment']   ?? null;
        $this->startTime      = $this->parseTimeToSeconds($data['startTime'] ?? 0);
        $this->endTime        = $this->parseTimeToSeconds($data['endTime']   ?? 0);
        $this->languageCodeHL = $data['languageCodeHL'] ?? null;
        $this->languageCodeJF = $data['languageCodeJF'] ?? null;
    }

    // ---------- Factories ----------

    public static function createFromStudyModel(array $study, string $languageCodeJF, ?string $languageCodeHL = null): self
    {
        return new self([
            'videoSource'    => $study['videoSource']  ?? null,
            'videoPrefix'    => $study['videoPrefix']  ?? null,
            'videoCode'      => $study['videoCode']    ?? null,
            'videoSegment'   => $study['videoSegment'] ?? null,
            'startTime'      => $study['startTime']    ?? 0,
            'endTime'        => $study['endTime']      ?? 0,
            'languageCodeJF' => $languageCodeJF,
            'languageCodeHL' => $languageCodeHL,
        ]);
    }

    public static function createFromPassageReferenceModel(
        PassageReferenceModel $ref,
        string $languageCodeJF,
        ?string $languageCodeHL = null
    ): self {
        return new self([
            'videoSource'    => $ref->getVideoSource(),
            'videoPrefix'    => $ref->getVideoPrefix(),
            'videoCode'      => $ref->getVideoCode(),
            'videoSegment'   => $ref->getVideoSegment(),
            'startTime'      => $ref->getStartTime() ?? 0,
            'endTime'        => $ref->getEndTime()   ?? 0,
            'languageCodeJF' => $languageCodeJF,
            'languageCodeHL' => $languageCodeHL,
        ]);
    }

    public static function createFromDatabase(array $db, string $languageCodeJF, ?string $languageCodeHL = null): self
    {
        return new self([
            'videoSource'    => $db['videoSource'] ?? null,
            'videoPrefix'    => $db['videoPrefix'] ?? null,
            'videoCode'      => $db['videoCode']   ?? null,
            'videoSegment'   => $db['segment']     ?? null,
            'startTime'      => $db['startTime']   ?? 0,
            'endTime'        => $db['endTime']     ?? 0,
            'languageCodeJF' => $languageCodeJF,
            'languageCodeHL' => $languageCodeHL,
        ]);
    }

    // ---------- URL building ----------

    /**
     * Builds (and stores) the Arclight URL if preconditions are met.
     * Returns null if not applicable.
     */
    public function buildArclightUrl(): ?string
    {
        if (($this->videoSource ?? '') !== 'arclight') {
            return $this->arclightUrl = null;
        }
        if ($this->languageCodeJF === null || $this->videoPrefix === null || $this->videoCode === null) {
            return $this->arclightUrl = null;
        }

        // Base player URL from config, e.g. "https://api.arclight.org/video/player/"
        $base = rtrim((string)Config::get('api.jvideo_player'), '/');

        // Common Arclight pattern often looks like: {base}/{prefix}/{code}/{langJF}{segment...}
        // Keep your existing pattern but prefer JF code (since you check for it):
        $url  = $base . '/' . $this->videoPrefix . '/' . $this->videoCode . '/' . $this->languageCodeJF;

        // Append segment string (may already contain ? or &)
        if ($this->videoSegment) {
            if ($this->videoSegment[0] !== '?' && $this->videoSegment[0] !== '&') {
                // normalize to query style if caller passed raw token
                $url .= '?' . $this->videoSegment;
            } else {
                $url .= $this->videoSegment;
            }
        }

        // Add start/end (seconds) if endTime > 0
        if ($this->endTime > 0) {
            $join = (strpos($url, '?') === false) ? '?' : '&';
            $url .= $join . 'start=' . $this->startTime . '&end=' . $this->endTime;
        }

        return $this->arclightUrl = $url;
    }

    public function getArclightUrl(): ?string
    {
        return $this->arclightUrl;
    }

    // ---------- Helpers ----------

    /**
     * Accepts int seconds, "SS", "MM:SS" or "HH:MM:SS". Returns seconds.
     */
    private function parseTimeToSeconds(int|string $time): int
    {
        if (is_int($time)) {
            return max(0, $time);
        }
        $time = trim((string)$time);
        if ($time === '' || strtolower($time) === 'start') {
            return 0;
        }
        if (strpos($time, ':') === false) {
            return ctype_digit($time) ? (int)$time : 0;
        }
        $parts = array_map('intval', explode(':', $time));
        if (count($parts) === 2) {
            [$m, $s] = $parts;
            return max(0, $m * 60 + $s);
        }
        if (count($parts) === 3) {
            [$h, $m, $s] = $parts;
            return max(0, $h * 3600 + $m * 60 + $s);
        }
        return 0;
    }

    public function getVideoSegmentString(): string
    {
        $segment = $this->videoSegment ?? '';
        $out = '';

        if ($segment !== '') {
            $out .= ($segment[0] === '?' || $segment[0] === '&') ? $segment : '?' . $segment;
        }
        if ($this->endTime > 0) {
            $join = ($out === '' || strpos($out, '?') === false) ? '?' : '&';
            $out .= $join . 'start=' . $this->startTime . '&end=' . $this->endTime;
        }
        return $out;
    }

    // ---------- Projection ----------

    public function toArray(): array
    {
        return [
            'videoSource'    => $this->videoSource,
            'videoPrefix'    => $this->videoPrefix,
            'videoCode'      => $this->videoCode,
            'videoSegment'   => $this->videoSegment,
            'startTime'      => $this->startTime,
            'endTime'        => $this->endTime,
            'arclightUrl'    => $this->arclightUrl,
            'languageCodeHL' => $this->languageCodeHL,
            'languageCodeJF' => $this->languageCodeJF,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // ---------- Getters / Setters (minimal you asked for) ----------

    public function getVideoCode(): ?string { return $this->videoCode; }
    public function getLanguageCodeHL(): ?string { return $this->languageCodeHL; }
    public function getLanguageCodeJF(): ?string { return $this->languageCodeJF; }
    public function setLanguageCodeJF(string $code): void { $this->languageCodeJF = $code; }

    public function setLanguageCodeHL(?string $code): void { $this->languageCodeHL = $code; }
    public function setVideoSegment(?string $segment): void { $this->videoSegment = $segment; }
    public function setStartTime(int|string $t): void { $this->startTime = $this->parseTimeToSeconds($t); }
    public function setEndTime(int|string $t): void { $this->endTime = $this->parseTimeToSeconds($t); }

    public function getVideoSource(): ?string { return $this->videoSource; }
    public function getVideoPrefix(): ?string { return $this->videoPrefix; }
}
