<?php

namespace App\Services;

use Exception;

class LoggerService
{
    private static $logFile = __DIR__ . '/../../logs/application.log';

    /**
     * Logs an error message.
     *
     * @param string $message The error message to log.
     */
    public static function logError(string $message): void
    {
        self::log('ERROR', $message);
    }

    /**
     * Logs a warning message.
     *
     * @param string $message The warning message to log.
     */
    public static function logWarning(string $message): void
    {
        self::log('WARNING', $message);
    }

    /**
     * Logs an informational message.
     *
     * @param string $message The informational message to log.
     */
    public static function logInfo(string $message): void
    {
        self::log('INFO', $message);
    }

    /**
     * Logs a message with a specified level.
     *
     * @param string $level The log level (e.g., ERROR, WARNING, INFO).
     * @param string $message The message to log.
     */
    private static function log(string $level, string $message): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $formattedMessage = "[{$timestamp}] [{$level}] {$message}" . PHP_EOL;

        try {
            file_put_contents(self::$logFile, $formattedMessage, FILE_APPEND);
        } catch (Exception $e) {
            // If logging fails, you might want to handle this (e.g., output to console)
            error_log("Logging failed: " . $e->getMessage());
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
