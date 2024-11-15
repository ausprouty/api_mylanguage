<?php

namespace App\Factories;

use App\Services\Web\BibleBrainConnectionService;

class BibleBrainConnectionFactory
{
    public function createModelForEndpoint(string $url): BibleBrainConnectionService
    {
        return new BibleBrainConnectionService($url);
    }
}
