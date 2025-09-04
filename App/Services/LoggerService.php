<?php

namespace App\Services;

use Exception;
use App\Configuration\Config;

class LoggerService
{
    /** @var string|null */
    private static $logFile;

    /** @var int|null cached numeric threshold per process */
    private static $minLevelNum;

    /**
     * Map a textual level to a numeric severity.
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

    /** Compute/cache the configured minimum level once per request/process. */
    private static function minLevelNum(): int
    {
        if (self::$minLevelNum === null) {
            $cfg = Config::get('log_level', 'info');
            self::$minLevelNum = self::levelNum((string) $cfg);
        }
        return self::$minLevelNum;
    }

    /** Check if a candidate level should be written. */
    private static function allowed(string $lvl): bool
    {
        return self::levelNum($lvl) >= self::minLevelNum();
    }

    /**
     * One-off override for this process/request only (useful for web debug).
     */
    public static function overrideLevel(string $lvl): void
    {
        self::$minLevelNum = self::levelNum($lvl);
    }

    /** Log an error. */
    public static function logError(string $context, $message): void
    {
        if (!is_string($message)) {
            $message = print_r($message, true);
        }
        self::log('ERROR', $context, $message);
    }

    /** Log a warning. */
    public static function logWarning(string $context, $message): void
    {
        if (!is_string($message)) {
            $message = print_r($message, true);
        }
        self::log('WARNING', $context, $message);
    }

    /** Log informational message. */
    public static function logInfo(string $context, $message): void
    {
        if (!is_string($message)) {
            $message = print_r($message, true);
        }
        self::log('INFO', $context, $message);
    }

    /** Log debug/trace. */
    public static function logDebug(string $context, $message): void
    {
        if (!is_string($message)) {
            $message = print_r($message, true);
        }
        self::log('DEBUG', $context, $message);
    }

    /**
     * Core writer. All public helpers funnel through here and are gated by
     * the configured level from Config::get('log_level').
     */
    private static function log(
        string $level,
        string $context,
        string $message
    ): void {
        if (!self::allowed($level)) {
            return;
        }

        if (!self::$logFile) {
            self::init();
        }

        $ts = date('Y-m-d H:i:s');
        $line = '[' . $ts . ']'
            . ' [' . $level . ']'
            . ' [' . $context . '] '
            . $message . PHP_EOL;

        try {
            file_put_contents(self::$logFile, $line, FILE_APPEND);
        } catch (Exception $e) {
            error_log('Logging failed: ' . $e->getMessage());
        }
    }

    /**
     * Resolve the log path from config and ensure the directory is usable.
     * Respects setLogFile() if called earlier.
     */
    private static function init(): void
    {
        if (self::$logFile) {
            return;
        }

        $dir = rtrim(Config::getDir('logs'), '/\\');
        $name = Config::get('log_file', 'application.log');
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
}
