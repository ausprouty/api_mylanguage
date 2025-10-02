<?php
declare(strict_types=1);

namespace App\Factories;

use App\Models\Bible\PassageReferenceModel;
use App\Models\Video\VideoModel;
use App\Support\Caster;

/**
 * VideoFactory
 *
 * Adapts various sources (DB rows, study arrays, PassageReferenceModel)
 * into a clean VideoModel with:
 *  - seconds stored in startTimeInSeconds / stopTimeInSeconds
 *  - trimmed/lower-cased codes handled in the model setters
 *  - segment token normalized (no leading ?/& or "segment=")
 */
final class VideoFactory
{
    /**
     * DB row -> VideoModel
     *
     * Accepts legacy columns like:
     *  - stopTime (e.g., "MM:SS" or "HH:MM:SS")
     *  - endTime / stop (string/int)
     *  - startTime / start (string/int)
     *  - segment (older name) or videoSegment (current)
     */
    public static function fromDbRow(array $db): VideoModel
    {
        $start = Caster::toSecondsOrZero(
            $db['startTimeInSeconds']
                ?? $db['startTime']
                ?? $db['start']
                ?? 0
        );

        $stop = Caster::toSecondsOrZero(
            $db['stopTimeInSeconds']
                ?? $db['stopTime']   // from SQL dump (char(6))
                ?? $db['endTime']
                ?? $db['stop']
                ?? 0
        );

        $segment = self::normalizeSegment(
            $db['videoSegment'] ?? $db['segment'] ?? null
        );

        return (new VideoModel())->populate([
            'id'                 => $db['id']          ?? 0,
            'title'              => $db['title']       ?? '',
            'verses'             => $db['verses']      ?? '',
            'videoSource'        => $db['videoSource'] ?? 'arclight',
            'videoPrefix'        => $db['videoPrefix'] ?? '',
            'videoCode'          => $db['videoCode']   ?? '-jf',
            'videoSegment'       => $segment,
            'startTimeInSeconds' => $start,
            'stopTimeInSeconds'  => $stop,
        ]);
    }

    /**
     * Study array -> VideoModel
     *
     * Accepts keys like:
     *  - startTime/start, endTime/stop (string/int/"MM:SS"/"HH:MM:SS")
     *  - videoSegment/segment (query-ish or raw)
     */
    public static function fromStudyArray(array $study): VideoModel
    {
        $start = Caster::toSecondsOrZero(
            $study['startTime'] ?? $study['start'] ?? 0
        );

        $stop = Caster::toSecondsOrZero(
            $study['endTime'] ?? $study['stop'] ?? 0
        );

        $segment = self::normalizeSegment(
            $study['videoSegment'] ?? $study['segment'] ?? null
        );

        return (new VideoModel())->populate([
            'id'                 => $study['id']           ?? 0,
            'title'              => $study['title']        ?? '',
            'verses'             => $study['verses']       ?? '',
            'videoSource'        => $study['videoSource']  ?? 'arclight',
            'videoPrefix'        => $study['videoPrefix']  ?? '',
            'videoCode'          => $study['videoCode']    ?? '-jf',
            'videoSegment'       => $segment,
            'startTimeInSeconds' => $start,
            'stopTimeInSeconds'  => $stop,
        ]);
    }

    /**
     * PassageReferenceModel -> VideoModel
     *
     * Title/verses are optional: if getters don't exist, they’re skipped.
     */
    public static function fromPassageReference(
        PassageReferenceModel $ref
    ): VideoModel {
        $start = Caster::toSecondsOrZero($ref->getStartTime() ?? 0);
        $stop  = Caster::toSecondsOrZero($ref->getEndTime()   ?? 0);

        $segment = self::normalizeSegment($ref->getVideoSegment());

        // Some PassageReferenceModel implementations may not expose title/verses
        $title  = \method_exists($ref, 'getTitle')
            ? (string)($ref->getTitle() ?? '')
            : '';
        $verses = \method_exists($ref, 'getVerses')
            ? (string)($ref->getVerses() ?? '')
            : '';

        return (new VideoModel())->populate([
            'title'              => $title,
            'verses'             => $verses,
            'videoSource'        => $ref->getVideoSource() ?? 'arclight',
            'videoPrefix'        => $ref->getVideoPrefix() ?? '',
            'videoCode'          => $ref->getVideoCode()   ?? '-jf',
            'videoSegment'       => $segment,
            'startTimeInSeconds' => $start,
            'stopTimeInSeconds'  => $stop,
        ]);
    }

    // -------------------------------------------------
    // Normalization helpers
    // -------------------------------------------------

    /**
     * Normalize a segment token to the raw value (e.g., "JESUS-123").
     *
     * Accepts:
     *   "?segment=JESUS-123" | "&segment=JESUS-123" -> "JESUS-123"
     *   "?JESUS-123"                                   -> "JESUS-123"
     *   "segment=JESUS-123&foo=bar"                    -> "JESUS-123"
     *   "JESUS-123"                                    -> "JESUS-123"
     *   null/''                                        -> ''
     */
    private static function normalizeSegment(mixed $raw): string
    {
        $s = Caster::toText($raw);
        if ($s === '') return '';

        // Leading ? or & indicates a query fragment.
        if ($s[0] === '?' || $s[0] === '&') {
            $query = \ltrim($s, '?&');
            \parse_str($query, $out);
            if (isset($out['segment'])) {
                return Caster::toText($out['segment']);
            }
            // "?JESUS-123" (no key)
            if ($query !== '' && !\str_contains($query, '=')) {
                return Caster::toText($query);
            }
            return '';
        }

        // "segment=JESUS-123&foo=bar"
        if (\strncasecmp($s, 'segment=', 8) === 0) {
            $after = \substr($s, 8);
            $amp   = \strpos($after, '&');
            if ($amp !== false) {
                $after = \substr($after, 0, $amp);
            }
            return Caster::toText($after);
        }

        // Already a raw token.
        return $s;
    }
}
