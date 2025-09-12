<?php

namespace App\Services\Web;

use App\Configuration\Config;
use App\Services\LoggerService;

class BibleBrainConnectionService extends WebsiteConnectionService
{
    /** BibleBrain v4 root */
    private const BASE_URL = 'https://4.dbt.io/api/';

    /**
     * @param string $endpoint e.g. "bibles" (no leading slash)
     * @param array  $params   extra query params (will be merged)
     * @param bool   $autoFetch  perform request immediately (default true)
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
        $q['v'] = $q['v'] ?? '4';
        $q['key'] = $apiKey;
        // Ask for JSON explicitly; helps Content-Type be correct
        $q['format'] = $q['format'] ?? 'json';

        $url = self::BASE_URL . $endpoint;
        $sep = (strpos($url, '?') !== false) ? '&' : '?';
        $url .= $sep . http_build_query($q, '', '&', PHP_QUERY_RFC3986);

        LoggerService::logInfo('BibleBrainConnectionService-url', $url);

        parent::__construct($url, $autoFetch, $salvageJson);
    }

    public static function getBaseUrl(): string
    {
        return self::BASE_URL;
    }
}
