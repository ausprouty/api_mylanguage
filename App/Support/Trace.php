<?php

namespace App\Support;

final class Trace
{
    /** @var string|null */
    private static $id = null;

    public static function init(?string $id = null): void
    {
        if ($id !== null && $id !== '') {
            self::$id = $id;
            return;
        }
        // Try to reuse incoming header (if you run behind a proxy)
        $incoming = $_SERVER['HTTP_X_TRACE_ID'] ?? null;
        if (is_string($incoming) && $incoming !== '') {
            self::$id = $incoming;
            return;
        }
        // Generate a new one
        self::$id = bin2hex(random_bytes(8)); // 16 hex chars
    }

    public static function id(): string
    {
        if (self::$id === null) {
            self::init();
        }
        return self::$id;
    }
}
