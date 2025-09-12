<?php

namespace App\Services\Web;

use App\Services\LoggerService;

class BibleWordConnectionService extends WebsiteConnectionService
{
    /** WordProject root */
    private const BASE_URL = 'https://wordproject.org/bibles/';

    /**
     * @param string $endpoint  e.g. "en/42/7.htm" (no leading slash)
     * @param bool   $salvageJson  allow trimming pre-JSON preamble (off by default)
     */
    public function __construct(string $endpoint, bool $salvageJson = false)
    {
        $endpoint = ltrim($endpoint, "/ \t\n\r\0\x0B");
        $url = self::BASE_URL . $endpoint;

        LoggerService::logInfo('BibleWordConnectionService-url', $url);

        // Auto-fetch with optional JSON salvage (usually HTML, so false ok)
        parent::__construct($url, true, $salvageJson);
    }
        /**
     * Back-compat convenience: get a single array with code/body/etc.
     * (Replaces the old idea of reading a $response property.)
     */
    public function response(): array
    {
        return $this->asArray();
    }

    /** Convenience: HTML body (most WordProject pages are HTML). */
    public function html(): string
    {
        return $this->getBody();
    }
}
