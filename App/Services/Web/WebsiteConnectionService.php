<?php

namespace App\Services\Web;

use Exception;
use App\Services\LoggerService;

class WebsiteConnectionService
{
    protected $url;
    public $response;
    
    public function __construct(string $url)
    {
        $this->url = $url;
        $this->connect();
        $this->decode(); // <<--- decode here
    }
    
    protected function connect()
    {
        try {
            LoggerService::logInfo('WebsiteConntectionService', $this->url);
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ]);
            $this->response = curl_exec($curl);

            // Check for cURL errors
            if (curl_errno($curl)) {
                $errorMessage = "cURL error: " . curl_error($curl);
                LoggerService::logError($errorMessage);
                throw new Exception($errorMessage);
            }

            curl_close($curl);

        } catch (Exception $e) {
            $errorMessage = "Failed to connect to the website: " . $e->getMessage();
            LoggerService::logError($errorMessage);
            throw new Exception($errorMessage);
        }
    }

    protected function decode()
    {
        $decoded = json_decode($this->response, true); // decode as array
        if (json_last_error() !== JSON_ERROR_NONE) {
            $errorMessage = "JSON decode error: " . json_last_error_msg();
            LoggerService::logError($errorMessage);
            throw new Exception($errorMessage);
        }

        // store decoded response as array
        $this->response = $decoded;
    }


    public function getResponse()
    {
        return $this->response;
    }
}
