<?php

namespace App\Services\Web;

use App\Services\Web\WebsiteConnectionService;
use App\Configuration\Config;

class BibleBrainConnectionService extends WebsiteConnectionService
{
    /**
     * The root URL for the BibleBrain API.
     */
    private const BASE_URL = 'https://4.dbt.io/api/';

    public function __construct(string $endpoint)
    {
        // Construct the full URL by combining the base URL and endpoint
        $url = self::BASE_URL . $endpoint;

        // Fetch the API key from the Config class
        $apiKey = Config::get('BIBLE_BRAIN_KEY');

        // Append the API version and key to the URL
        if (strpos($url, '?') !== false) {
            $url .= '&v=4&key=' . $apiKey;
        } else {
            $url .= '?v=4&key=' . $apiKey;
        }
 
        // Call the parent constructor to initialize the URL and connection
        parent::__construct($url);

        // Decode the JSON response after the connection
        $this->response = json_decode($this->response);
    }
}
