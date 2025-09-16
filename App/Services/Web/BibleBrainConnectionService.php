<?php
declare(strict_types=1);

namespace App\Services\Web;

use App\Configuration\Config;
use App\Services\LoggerService;

class BibleBrainConnectionService extends WebsiteConnectionService
{
    /**
     * Resolve the BibleBrain base URL from config.
     * Falls back to https://4.dbt.io/api/ if not set.
     */
    private static function baseUrl(): string
    {
        // You used `endpoints.*` earlier in DI; keep that key name here.
        $root = (string) Config::get('endpoints.biblebrain', 'https://4.dbt.io');
        $root = rtrim($root, "/ \t\n\r\0\x0B");

        // Ensure it ends with '/api'
        if (!str_ends_with($root, '/api')) {
            $root .= '/api';
        }
        return $root . '/';
    }

    /**
     * @param string $endpoint  e.g. "bibles" (no leading slash)
     * @param array  $params    extra query params (will be merged)
     * @param bool   $autoFetch perform request immediately (default true)
     * @param bool   $salvageJson trim pre-JSON junk if present (default true)
     */
    public function __construct(
        string $endpoint,
        array $params = [],
        bool $autoFetch = true,
        bool $salvageJson = true
    ) {
        $endpoint = ltrim($endpoint, "/ \t\n\r\0\x0B");

        $apiKey = (string) Config::get('api.bible_brain_key');

        // Required params
        $q = $params;
        $q['v']      = $q['v']      ?? '4';
        $q['key']    = $q['key']    ?? $apiKey;
        $q['format'] = $q['format'] ?? 'json';

        $url = self::baseUrl() . $endpoint;
        $sep = (strpos($url, '?') !== false) ? '&' : '?';
        $url .= $sep . http_build_query($q, '', '&', PHP_QUERY_RFC3986);

        LoggerService::logInfo('BibleBrainConnectionService-url', $url);

        parent::__construct($url, $autoFetch, $salvageJson);
    }

    public static function getBaseUrl(): string
    {
        return self::baseUrl();
    }
}
