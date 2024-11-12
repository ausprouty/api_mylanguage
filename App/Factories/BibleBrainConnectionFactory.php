<?php

namespace App\Factories;

use App\Models\Data\BibleBrainConnectionModel;

class BibleBrainConnectionFactory
{
    public function createModelForEndpoint(string $url): BibleBrainConnectionModel
    {
        return new BibleBrainConnectionModel($url);
    }
}
