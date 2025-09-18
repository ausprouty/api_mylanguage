<?php

namespace App\Services;

use Exception;
use App\Support\Trace;
use App\Configuration\Config;

/**
 * LoggerService
 *
 * Single-file text logger with:
 * - Configurable level threshold (debug|info|warning|error|critical)
 * - One-line entries with JSON context (traceId, method, path, ip)
 * - File target chosen from config; falls back to temp if unwritable
 * - Optional mirroring to PHP error_log() or a specific file
 *
 * Config (supports legacy root keys and nested `logging.*`):
 *
 * // Legacy (still supported)
 * 'log_level'            => 'info',
 * 'log_file'             => 'application.log',
 * 'log_cli_file'         => 'translation-a.log',
 * 'log_mirror_error_log' => true, // or "C:/path/to/php_errors.log"
 * 'logs'                 => 'C:/ampp82/logs', // via Config::getDir('logs')
 *
 * // Preferred nested
 * 'logging' => [
 *   'mode'                 => 'write_log',       // informational only
 *   'level'                => 'info',
 *   'file'                 => 'application.log',
 *   'cli_file'             => 'translation-a.log',
 *   'log_mirror_error_log' => true               // or absolute path string
 * ]
 */
class LoggerService
{
    /** @var string|null Absolute path of the active log file. */
    private static ?string $logFile = null;

    /** @var int|null Cached numeric threshold for this process. */
    private static ?int $minLevelNum = null;

    /**
     * Mirror settings (lazy-resolved):
     * - enabled?: true/false
     * - file?: null means use PHP error_log(); string means append to path
     */
    private static ?bool $mirrorEnabled = null;
    private static ?string $mirrorFile  = null;

    /** Level mapping: debug(10) < info(20) < warning(30) < error(40) < critical(50) */
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
        return isset($map[$k]) ? $map[$k] : 20;  // default INFO
    }

    /**
     * Resolve the minimum level once per request/process.
     * Prefers `logging.level`, falls back to legacy `log_level`.
     */
    private static function minLevelNum(): int
    {
        if (self::$minLevelNum !== null) {
            return self::$minLevelNum;
        }

        $lvl = Config::get('logging.level', null);
        if ($lvl === null) {
            $lvl = Config::get('log_level', 'info');
        }

        self::$minLevelNum = self::levelNum((string) $lvl);
        return self::$minLevelNum;
    }

    /**
     * True if a candidate level should be written given the threshold.
     */
    private static function allowed(string $lvl): bool
    {
        return self::levelNum($lvl) >= self::minLevelNum();
    }

    /**
     * Public override for the current process (useful for ad-hoc web debug).
     */
    public static function overrideLevel(string $lvl): void
    {
        self::$minLevelNum = self::levelNum($lvl);
    }

    // ---------- Public convenience methods (structured logging) ----------

    /** @param array<string,mixed> $ctx */
    public static function logError(string $ctxName, $msg, array $ctx = []): void
    {
        self::log('ERROR', $ctxName, $msg, $ctx);
    }

    /** @param array<string,mixed> $ctx */
    public static function logCritical(
        string $ctxName,
        $msg,
        array $ctx = []
    ): void {
        self::log('CRITICAL', $ctxName, $msg, $ctx);
    }

    /** @param array<string,mixed> $ctx */
    public static function logWarning(
        string $ctxName,
        $msg,
        array $ctx = []
    ): void {
        self::log('WARNING', $ctxName, $msg, $ctx);
    }

    /** @param array<string,mixed> $ctx */
    public static function logInfo(
        string $ctxName,
        $msg,
        array $ctx = []
    ): void {
        self::log('INFO', $ctxName, $msg, $ctx);
    }

    /** @param array<string,mixed> $ctx */
    public static function logDebug(
        string $ctxName,
        $msg,
        array $ctx = []
    ): void {
        self::log('DEBUG', $ctxName, $msg, $ctx);
    }

    // ------------------------------- Core --------------------------------

    /**
     * Core writer: one line of text with JSON context.
     *
     * Format:
     * [YYYY-mm-dd HH:ii:ss] [LEVEL] [Context] Message {"traceId":"...","k":"v"}
     *
     * @param array<string,mixed> $ctx
     */
    private static function log(
        string $level,
        string $context,
        $message,
        array $ctx = []
    ): void {
        if (!self::allowed($level)) {
            return;
        }

        if (!self::$logFile) {
            self::init();
        }

        $msg = is_string($message) ? $message : print_r($message, true);

        // Attach minimal request/trace context
        $ctx = array_merge([
            'traceId' => Trace::id(),
            'method'  => $_SERVER['REQUEST_METHOD'] ?? null,
            'path'    => $_SERVER['REQUEST_URI'] ?? null,
            'ip'      => $_SERVER['REMOTE_ADDR'] ?? null,
        ], $ctx);

        $ts   = date('Y-m-d H:i:s');
        $line = '[' . $ts . ']'
              . ' [' . strtoupper($level) . ']'
              . ' [' . $context . '] '
              . self::compactOneLine($msg)
              . ' ' . self::encodeJsonSafe($ctx);

        try {
            file_put_contents(self::$logFile, $line . PHP_EOL, FILE_APPEND);
            self::mirrorLine($line);
        } catch (Exception $e) {
            error_log('Logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Initialize file target from config; ensure directory exists/writable.
     * Prefers `logging.file`/`logging.cli_file`, falls back to legacy keys.
     */
    private static function init(): void
    {
        if (self::$logFile) {
            return;
        }

        // Directory selection: Config::getDir('logs') should point to a folder.
        $dir = rtrim((string) Config::getDir('logs'), '/\\');

        // File name selection: prefer nested logging.* keys
        $isCli = (php_sapi_name() === 'cli');
        $name  = null;

        if ($isCli) {
            $name = Config::get('logging.cli_file', null);
            if ($name === null) {
                $name = Config::get('log_cli_file', null);
            }
        }

        if ($name === null) {
            $name = Config::get('logging.file', null);
            if ($name === null) {
                $name = Config::get('log_file', 'application.log');
            }
        }

        $path = $dir . DIRECTORY_SEPARATOR . (string) $name;

        // Ensure directory exists and is writable; otherwise fallback to system temp.
        if (!is_dir($dir)) {
            @mkdir($dir, 0755, true);
        }
        if (!is_dir($dir) || !is_writable($dir)) {
            error_log("LoggerService: '$dir' not writable; using temp dir");
            $tmp  = rtrim(sys_get_temp_dir(), '/\\');
            $path = $tmp . DIRECTORY_SEPARATOR . (string) $name;
        }

        self::$logFile = $path;
    }

    /**
     * Resolve "mirror to error log" behavior.
     * Supports:
     * - false/null : no mirroring
     * - true       : mirror via PHP error_log()
     * - string     : append to that file path
     *
     * Prefers `logging.log_mirror_error_log`, falls back to legacy root key.
     */
    private static function mirrorEnabled(): bool
    {
        if (self::$mirrorEnabled !== null) {
            return self::$mirrorEnabled;
        }

        $val = Config::get('logging.log_mirror_error_log', null);
        if ($val === null) {
            $val = Config::get('log_mirror_error_log', null);
        }

        if ($val === true) {
            self::$mirrorEnabled = true;
            self::$mirrorFile    = null;   // use PHP error_log()
        } elseif (is_string($val) && $val !== '') {
            self::$mirrorEnabled = true;
            self::$mirrorFile    = $val;   // target file
        } else {
            self::$mirrorEnabled = false;
            self::$mirrorFile    = null;
        }

        return self::$mirrorEnabled;
    }

    /**
     * If mirroring is enabled, send the already-formatted log line either
     * to PHP's error_log() or to the configured mirror file.
     */
    private static function mirrorLine(string $line): void
    {
        if (!self::mirrorEnabled()) {
            return;
        }

        if (self::$mirrorFile) {
            // Best-effort (avoid breaking request flow on permission errors)
            @file_put_contents(self::$mirrorFile, $line . PHP_EOL, FILE_APPEND);
            return;
        }

        error_log($line);
    }

    // ------------------------------- Utils -------------------------------

    /** Compact multi-line strings into a single line for log entries. */
    private static function compactOneLine(string $s): string
    {
        $s = str_replace(["\r\n", "\r"], "\n", $s);
        $s = preg_replace('/\s+/', ' ', $s);
        return trim($s);
    }

    /**
     * Safe JSON encode for context; never throws.
     * @param array<string,mixed> $ctx
     */
    private static function encodeJsonSafe(array $ctx): string
    {
        $json = json_encode(
            $ctx,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        return ($json === false) ? '{}' : $json;
    }

    // ------------------------------ Overrides ----------------------------

    /**
     * Allow callers to override the log file at runtime (tests, one-off scripts).
     */
    public static function setLogFile(string $filePath): void
    {
        self::$logFile = $filePath;
    }
}
