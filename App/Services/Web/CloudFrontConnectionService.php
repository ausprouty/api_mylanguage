<?php
namespace App\Services\Web;

use Exception;

class CloudFrontConnectionService
{
    protected $url;
    public $response;

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->connect();
    }

    protected function connect()
    {
        try {
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
            
            $data = curl_exec($curl);

            if (curl_errno($curl)) {
                throw new Exception("cURL error: " . curl_error($curl));
            }

            curl_close($curl);

            $this->response = json_decode($data);

        } catch (Exception $e) {
            // Log the exception
            writeLogDebug('CloudFrontConnectionService', $e->getMessage());

            // Rethrow the exception to notify the caller of the failure
            throw new Exception("Failed to connect to CloudFront: " . $e->getMessage());
        }
    }

    public function getResponse()
    {
        return $this->response;
    }
}
