<?php
declare(strict_types=1);

namespace App\Http;

interface HttpClientInterface
{
    /** Return array like ['status'=>int,'headers'=>array,'body'=>string] */
    public function get(string $url, ?array $options = null): array;
    // add post/put if your Curl/Retry clients implement them
}
