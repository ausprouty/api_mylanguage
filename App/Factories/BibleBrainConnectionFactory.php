<?php
declare(strict_types=1);

namespace App\Factories;

use App\Services\Web\BibleBrainConnectionService;

final class BibleBrainConnectionFactory
{
    /**
     * Build a connection for an API endpoint, e.g. "bibles" or "text/verse".
     * $params become query string arguments (v, key, format are filled in by the service).
     */
    public function fromPath(
        string $endpoint,
        array $params = [],
        bool $autoFetch = true,
        bool $salvageJson = true
    ): BibleBrainConnectionService {
        return new BibleBrainConnectionService($endpoint, $params, $autoFetch, $salvageJson);
    }
}
