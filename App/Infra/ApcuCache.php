<?php
declare(strict_types=1);

namespace App\Infra;

use Psr\SimpleCache\CacheInterface;
use DateInterval;

final class ApcuCache implements CacheInterface
{
    private function ttlSeconds(null|int|DateInterval $ttl): int
    {
        if ($ttl === null) return 0;
        if ($ttl instanceof DateInterval) {
            $dt = new \DateTimeImmutable('now', new \DateTimeZone('UTC'));
            return (int)$dt->add($ttl)->format('U') - (int)$dt->format('U');
        }
        return max(0, (int)$ttl);
    }

    public function get($key, $default = null)
    {
        $ok = false;
        $val = apcu_fetch((string)$key, $ok);
        return $ok ? $val : $default;
    }

    public function set($key, $value, $ttl = null): bool
    {
        return apcu_store((string)$key, $value, $this->ttlSeconds($ttl));
    }

    public function delete($key): bool
    {
        return (bool) apcu_delete((string)$key);
    }

    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    public function getMultiple($keys, $default = null): iterable
    {
        $keys = array_map('strval', is_array($keys) ? $keys : iterator_to_array($keys));
        $found = apcu_fetch($keys);
        $out = [];
        foreach ($keys as $k) $out[$k] = $found[$k] ?? $default;
        return $out;
    }

    public function setMultiple($values, $ttl = null): bool
    {
        $ttl = $this->ttlSeconds($ttl);
        $ok = apcu_store($values, null, $ttl);
        // apcu_store returns an array of failed keys or true
        return $ok === true || $ok === [];
    }

    public function deleteMultiple($keys): bool
    {
        $keys = is_array($keys) ? $keys : iterator_to_array($keys);
        $res = apcu_delete($keys);
        // returns array of keys that couldn't be deleted
        return $res === [] || $res === true;
    }

    public function has($key): bool
    {
        return apcu_exists((string)$key);
    }
}
