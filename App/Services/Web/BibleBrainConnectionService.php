<?php

namespace App\Services\Web;

use App\Services\Web\WebsiteConnectionService;
use App\Configuration\Config;

class BibleBrainConnectionService extends WebsiteConnectionService
{
  public function __construct(string $url)
  {
    // Fetch the API key from the Config class
    $apiKey = Config::get('BIBLE_BRAIN_KEY');

    // Check if the URL already contains a "?" character
    if (strpos($url, '?') !== false) {
      // If "?" is found, append with "&"
      $url .= '&v=4&key=' . $apiKey;
    } else {
      // If no "?" is found, append with "?"
      $url .= '?v=4&key=' . $apiKey;
    }
  
    // Call the parent constructor to initialize the URL and connection
    parent::__construct($url);

    // Decode the JSON response after the connection
    $this->response = json_decode($this->response);
  }
}
