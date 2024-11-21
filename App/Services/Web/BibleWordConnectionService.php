<?php

namespace App\Services\Web;

use App\Services\Web\WebsiteConnectionService;
use App\Configuration\Config;

class BibleWordConnectionService extends WebsiteConnectionService
{
    /**
     * The root URL for the BibleGateway API.
     */
    private const BASE_URL = 'https://wordproject.org/bibles/';

    public function __construct(string $endpoint)
    {
        // Construct the full URL by combining the base URL and endpoint
        $url = self::BASE_URL . $endpoint;
        


        // Call the parent constructor to initialize the URL and connection
        parent::__construct($url);

    }
}
