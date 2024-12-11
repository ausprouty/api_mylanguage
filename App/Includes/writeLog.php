<?php

namespace App\Services;

use App\Configuration\Config;

class LoggerService
{
    private static function ensureLogDirectoryExists()
    {
        $logDirectory = Config::getDir('logs');
        if (!file_exists($logDirectory)) {
            mkdir($logDirectory, 0755, true);
        }
    }

    private static function getFilePath(string $filename, string $prefix = ''): string
    {
        $logMode = Config::get('logging.mode');
        $timestamp = ($logMode === 'write_time_log') ? time() . '-' : '';
        $logDirectory = Config::getDir('logs');

        return $logDirectory . $prefix . $timestamp . $filename . '.txt';
    }

    public static function writeLog(string $filename, $content)
    {
        $logMode = Config::get('logging.mode');
        if ($logMode !== 'write_log' && $logMode !== 'write_time_log') {
            return;
        }

        $filePath = self::getFilePath($filename);
        $text = self::varDumpRet($content);

        self::ensureLogDirectoryExists();
        file_put_contents($filePath, $text);
    }

    public static function writeLogAppend(string $filename, $content)
    {
        $filePath = self::getFilePath($filename, 'APPEND-');
        $text = self::varDumpRet($content);

        self::ensureLogDirectoryExists();
        file_put_contents($filePath, $text, FILE_APPEND | LOCK_EX);
    }

    public static function writeLogDebug(string $filename, $content)
    {
        $filePath = self::getFilePath($filename, 'DEBUG-');
        $text = self::varDumpRet($content);

        self::ensureLogDirectoryExists();
        file_put_contents($filePath, $text);
    }

    public static function writeLogError(string $filename, $content)
    {
        $filePath = self::getFilePath($filename, 'ERROR-');
        $text = self::varDumpRet($content);

        self::ensureLogDirectoryExists();
        file_put_contents($filePath, $text);
    }

    private static function varDumpRet($mixed): string
    {
        ob_start();
        var_dump($mixed);
        return ob_get_clean();
    }
}
