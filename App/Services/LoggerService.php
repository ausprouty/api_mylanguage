<?php

namespace App\Services;

use Exception;
use App\Configuration\Config;

class LoggerService
{
    private static $logFile;

    /**
     * Logs an error message.
     *
     * @param string $context Context or location of the error.
     * @param string $message The error message to log.
     */
    public static function logError(string $context, string $message): void
    {
        self::log('ERROR', $context, $message);
    }

    /**
     * Logs a warning message.
     *
     * @param string $context Context or location of the warning.
     * @param string $message The warning message to log.
     */
    public static function logWarning(string $context, string $message): void
    {
        self::log('WARNING', $context, $message);
    }

    /**
     * Logs an informational message.
     *
     * @param string $context Context or location of the info log.
     * @param string $message The informational message to log.
     */
    public static function logInfo(string $context, string $message): void
    {
        self::log('INFO', $context, $message);
    }

    /**
     * Logs a message with a specified level.
     *
     * @param string $level The log level (e.g., ERROR, WARNING, INFO).
     * @param string $context Context or location where the log is triggered.
     * @param string $message The message to log.
     */
    private static function log(string $level, string $context, string $message): void
    {
        // Lazy initialization of log file path
        if (!self::$logFile) {
            self::init();
        }

        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] [{$level}] [{$context}] {$message}" . PHP_EOL;

        try {
            file_put_contents(self::$logFile, $formattedMessage, FILE_APPEND);
        } catch (Exception $e) {
            // If logging fails, output to PHP error log
            error_log("Logging failed: " . $e->getMessage());
        }
    }

    /**
     * Initializes the log file path.
     * This is called automatically if not set.
     */
    private static function init(): void
    {
        if (!self::$logFile) {
            self::$logFile = Config::getDir('logs') . 'application.log';
        }
    }

    /**
     * Set a custom log file path if needed.
     *
     * @param string $filePath The path to the log file.
     */
    public static function setLogFile(string $filePath): void
    {
        self::$logFile = $filePath;
    }
}
