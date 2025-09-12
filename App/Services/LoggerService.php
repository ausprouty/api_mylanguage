<?php

namespace App\Services;

use Exception;
use App\Support\Trace;
use App\Configuration\Config;

class LoggerService
{
    /** @var string|null */
    private static $logFile;

    /** @var int|null cached numeric threshold per process */
    private static $minLevelNum;

    /** @var bool whether to also send to PHP error_log */
    private static $mirrorToErrorLog;

    /**
     * Map textual level to numeric severity.
     * debug(10) < info(20) < warning(30) < error(40) < critical(50)
     */
    private static function levelNum(string $lvl): int
    {
        $map = [
            'debug'    => 10,
            'info'     => 20,
            'warning'  => 30,
            'error'    => 40,
            'critical' => 50,
        ];
        $k = strtolower($lvl);
        return isset($map[$k]) ? $map[$k] : 20; // default INFO
    }

    /** Compute/cache the configured minimum level once per request. */
    private static function minLevelNum(): int
    {
        if (self::$minLevelNum === null) {
            $cfg = (string) Config::get('log_level', 'info');
            self::$minLevelNum = self::levelNum($cfg);
        }
        return self::$minLevelNum;
    }

    private static function mirror(): bool
    {
        if (self::$mirrorToErrorLog === null) {
            self::$mirrorToErrorLog = (bool) Config::getBool(
                'log_mirror_error_log',
                false
            );
        }
        return self::$mirrorToErrorLog;
    }

    /** Check if a candidate level should be written. */
    private static function allowed(string $lvl): bool
    {
        return self::levelNum($lvl) >= self::minLevelNum();
    }

    /** One-off override for this process/request only (useful for web debug). */
    public static function overrideLevel(string $lvl): void
    {
        self::$minLevelNum = self::levelNum($lvl);
    }

    /** Public helpers (now accept optional $ctx = [] for structured data). */
    public static function logError(string $ctxName, $msg, array $ctx = []): void
    {
        self::log('ERROR', $ctxName, $msg, $ctx);
    }

    public static function logCritical(
        string $ctxName,
        $msg,
        array $ctx = []
    ): void {
        self::log('CRITICAL', $ctxName, $msg, $ctx);
    }

    public static function logWarning(
        string $ctxName,
        $msg,
        array $ctx = []
    ): void {
        self::log('WARNING', $ctxName, $msg, $ctx);
    }

    public static function logInfo(string $ctxName, $msg, array $ctx = []): void
    {
        self::log('INFO', $ctxName, $msg, $ctx);
    }

    public static function logDebug(string $ctxName, $msg, array $ctx = []): void
    {
        self::log('DEBUG', $ctxName, $msg, $ctx);
    }

    /**
     * Core writer: text line with JSON context (includes traceId).
     * Format:
     * [YYYY-mm-dd HH:ii:ss] [LEVEL] [Context] Message {"traceId":"...","k":"v"}
     */
    private static function log(
        string $level,
        string $context,
        $message,
        array $ctx = []
    ): void {
        if (!self::allowed($level)) return;

        if (!self::$logFile) self::init();

        $msg = is_string($message) ? $message : print_r($message, true);

        // Always attach traceId + lightweight request fields
        $ctx = array_merge([
            'traceId' => Trace::id(),
            'method'  => $_SERVER['REQUEST_METHOD'] ?? null,
            'path'    => $_SERVER['REQUEST_URI'] ?? null,
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
        ], $ctx);

        $ts = date('Y-m-d H:i:s');
        $line = '[' . $ts . ']'
            . ' [' . strtoupper($level) . ']'
            . ' [' . $context . '] '
            . self::compactOneLine($msg)
            . ' ' . self::encodeJsonSafe($ctx)
            . PHP_EOL;

        try {
            file_put_contents(self::$logFile, $line, FILE_APPEND);
            if (self::mirror()) error_log($line);
        } catch (Exception $e) {
            error_log('Logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Resolve the log path and ensure the directory is usable.
     * Respects setLogFile() if called earlier.
     */
    private static function init(): void
    {
        if (self::$logFile) return;

        $dir = rtrim((string) Config::getDir('logs'), '/\\');
        $name = (string) Config::get('log_file', 'application.log');
        $path = $dir . DIRECTORY_SEPARATOR . $name;

        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            error_log("LoggerService: '$dir' not writable; using temp dir");
            $tmp = rtrim(sys_get_temp_dir(), '/\\');
            $path = $tmp . DIRECTORY_SEPARATOR . $name;
        }

        self::$logFile = $path;
    }

    /** Allow callers to override the target file path. */
    public static function setLogFile(string $filePath): void
    {
        self::$logFile = $filePath;
    }

    /** --- Helpers ------------------------------------------------------- */

    /** Make multi-line messages compact for single-line logs. */
    private static function compactOneLine(string $s): string
    {
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }

    /** Safe JSON encode for context (no exceptions; fallback to "{}"). */
    private static function encodeJsonSafe(array $ctx): string
    {
        $json = json_encode(
            $ctx,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        if ($json === false) {
            $json = '{}';
        }
        return $json;
    }
}
