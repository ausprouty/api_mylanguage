<?php

namespace App\Services\Web;

use App\Services\LoggerService;

class YouVersionConnectionService extends WebsiteConnectionService
{
    /** YouVersion reader root */
    private const BASE_URL = 'https://www.bible.com/bible/';

    /**
     * @param string $endpoint e.g. "111/JHN.3.NIV" (no leading slash needed)
     * @param bool   $autoFetch  perform request immediately (default true)
     * @param bool   $salvageJson trim pre-JSON junk (HTML expected ⇒ false)
     */
    public function __construct(
        string $endpoint,
        bool $autoFetch = true,
        bool $salvageJson = false
    ) {
        $endpoint = ltrim($endpoint, "/ \t\n\r\0\x0B");
        $url = self::BASE_URL . $endpoint;

        LoggerService::logInfo('YouVersionConnectionService-url', $url);

        // YouVersion typically returns HTML; leave salvageJson disabled.
        parent::__construct($url, $autoFetch, $salvageJson);
    }

    public static function getBaseUrl(): string
    {
        return self::BASE_URL;
    }
}
