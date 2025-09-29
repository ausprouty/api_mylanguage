<?php
declare(strict_types=1);

namespace App\Support;

final class Async
{
    /**
     * Spawn a detached PHP process cross-platform.
     * If $logFile is given, stdoutstderr are appended there.
     * $cwd is optional working directory.
     */
    public static function php(
        string $script, 
        array $args = [], 
        ?string $logFile = null, 
        ?string $cwd = null): void
     {
        $php = escapeshellarg(PHP_BINARY);
        $cmd = $php . ' ' . escapeshellarg($script);
        foreach ($args as $a) {
            $cmd .= ' ' . escapeshellarg($a);
        }

        // Ensure log directory if logging
        if ($logFile) {
            $dir = dirname($logFile);
            if (!is_dir($dir)) { @mkdir($dir, 0777, true); }
            $redir = ' >> ' . escapeshellarg($logFile) . ' 2>&1';
        } else {
            // discard output if no log requested
            $redir = stripos(PHP_OS_FAMILY, 'Windows') === 0 ? ' >NUL 2>&1' : ' >/dev/null 2>&1';
        }

        // Change directory if requested (best effort)
        $restore = null;
        if ($cwd) {
            $restore = @getcwd();
            @chdir($cwd);
        }

        if (stripos(PHP_OS_FAMILY, 'Windows') === 0) {
           // Use START with empty title and /B to detach; cmd.exe required
            // Note: keep quotes safe by building one shell line.
            $line = 'cmd /c start "" /B ' . $cmd . $redir;
            @pclose(@popen($line, 'r'));
         } else {  
            // nohup  background on *nix
            @exec('nohup ' . $cmd . $redir . ' &');
        }

        if ($restore !== null) {
            @chdir($restore);
         }
     }
 }