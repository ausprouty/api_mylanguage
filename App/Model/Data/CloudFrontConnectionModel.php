<?php
/*  see https://documenter.getpostman.com/view/12519377/Tz5p6dp7
*/
namespace App\Model\Data;

use Exception as Exception;

class CloudFrontConnectionModel
{
    protected $url;
    public $response;
    
    public function __construct(string $url){
      $this->url = $url;
      $this->connect();
    }
    protected function connect() {
        try {
            $curl = curl_init();
            curl_setopt_array($curl, array(
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 0,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
            ));
            $data = curl_exec($curl);
    
            // Check for cURL errors
            if (curl_errno($curl)) {
                throw new Exception("cURL error: " . curl_error($curl));
            }
    
            curl_close($curl);
    
            // Decode the JSON response
            $this->response = json_decode($data);
    
        } catch (Exception $e) {
            // Log the exception
            writeLogDebug('CloudFrontConnection-34', $e->getMessage());
    
            // Rethrow the exception for further handling
            throw new Exception("Failed to connect to the website: " . $e->getMessage());
        }
    }

}