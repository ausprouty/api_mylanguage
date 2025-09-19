<?php
declare(strict_types=1);

namespace App\Support;

final class Async
{
    /** Spawn a detached PHP process cross-platform. */
    public static function php(string $script, array $args = []): void
    {
        $php = escapeshellarg(PHP_BINARY);
        $cmd = $php . ' ' . escapeshellarg($script);
        foreach ($args as $a) $cmd .= ' ' . escapeshellarg($a);

        if (stripos(PHP_OS_FAMILY, 'Windows') === 0) {
            // start /B detaches on Windows
            @pclose(@popen("start /B " . $cmd, "r"));
        } else {
            // nohup detaches on *nix
            @exec($cmd . " >/dev/null 2>&1 &");
        }
    }
}
