<?php

namespace App\Services\Web;

use App\Services\LoggerService;

class BibleGatewayConnectionService extends WebsiteConnectionService
{
    /** Public site root (HTML pages) */
    private const BASE_URL = 'https://www.biblegateway.com';

    /**
     * @param string $endpoint e.g. "/passage/?search=John+3%3A16"
     *                         (leading slash optional)
     * @param bool   $autoFetch  perform request immediately (default true)
     * @param bool   $salvageJson trim pre-JSON junk (off: HTML expected)
     */
    public function __construct(
        string $endpoint,
        bool $autoFetch = true,
        bool $salvageJson = false
    ) {
        $endpoint = '/' . ltrim($endpoint, "/ \t\n\r\0\x0B");

        $url = self::BASE_URL . $endpoint;

        LoggerService::logInfo('BibleGatewayConnectionService-url', $url);

        // BibleGateway returns HTML; keep salvageJson false by default.
        parent::__construct($url, $autoFetch, $salvageJson);
    }

    public static function getBaseUrl(): string
    {
        return self::BASE_URL;
    }
}
