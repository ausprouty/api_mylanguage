<?php

namespace App\Services\Web;

use Exception;
use App\Services\LoggerService;

class WebsiteConnectionService
{
    protected string $url;
    protected string $body = '';
    protected ?array $json = null;
    protected int $httpCode = 0;
    protected ?string $contentType = null;

    public function __construct(string $url)
    {
        $this->url = $url;
        $this->connect();
        $this->decodeIfJson(); // ← now conditional
    }

    protected function connect(): void
    {
        try {
            LoggerService::logInfo(
                'WebsiteConnectionService-23',
                $this->url
            );

            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $this->url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_ENCODING => '',
                CURLOPT_MAXREDIRS => 10,
                CURLOPT_TIMEOUT => 20,
                CURLOPT_FOLLOWLOCATION => true,
                CURLOPT_HTTP_VERSION => CURL_HTTP_VERSION_1_1,
                CURLOPT_CUSTOMREQUEST => 'GET',
                CURLOPT_HEADER => false,
                CURLOPT_USERAGENT => 'HL/WebsiteConnectionService',
            ]);

            $result = curl_exec($curl);

            if ($result === false) {
                $msg = 'cURL error: ' . curl_error($curl);
                LoggerService::logError('WebsiteConnectionService-48', $msg);
                curl_close($curl);
                throw new Exception($msg);
            }

            $this->body = (string) $result;
            $this->httpCode = (int) curl_getinfo(
                $curl,
                CURLINFO_RESPONSE_CODE
            );
            $this->contentType = curl_getinfo(
                $curl,
                CURLINFO_CONTENT_TYPE
            ) ?: null;
        
            curl_close($curl);
            LoggerService::logInfo(
                'WebsiteConnectionService-66',
                $this->contentType
            );

            if ($this->httpCode >= 400) {
                $msg = 'HTTP ' . $this->httpCode . ' from ' . $this->url;
                LoggerService::logError('WebsiteConnectionService-66', $msg);
                throw new Exception($msg);
            }
        } catch (Exception $e) {
            $msg = 'Failed to connect: ' . $e->getMessage();
            LoggerService::logError('WebsiteConnectionService-72', $msg);
            throw new Exception($msg);
        }
    }

    protected function decodeIfJson(): void
    {
        if (!$this->isJsonResponse()) {
            // Not JSON; leave $json as null and keep $body intact.
            return;
        }

        try {
            $this->json = json_decode(
                $this->body,
                true,
                512,
                JSON_THROW_ON_ERROR
            );
        } catch (\JsonException $e) {
            $msg = 'JSON decode error: ' . $e->getMessage();
            LoggerService::logError('WebsiteConnectionService-92', $msg);
            // Do NOT throw here unless you want JSON to be mandatory.
            // Keep raw body; leave $json as null.
            $this->json = null;
        }
    }

    protected function isJsonResponse(): bool
    {
        // Prefer Content-Type
        if ($this->contentType &&
            stripos($this->contentType, 'json') !== false) {
            return true;
        }

        // Fallback: quick shape check
        $trim = ltrim($this->body);
        return $trim !== '' && ($trim[0] === '{' || $trim[0] === '[');
    }

    /** Raw response body (HTML, JSON text, etc.). */
    public function getBody(): string
    {
        return $this->body;
    }

    /** Decoded JSON as array, or null if not JSON / failed to decode. */
    public function getJson(): ?array
    {
        return $this->json;
    }

    public function getHttpCode(): int
    {
        return $this->httpCode;
    }

    public function getContentType(): ?string
    {
        return $this->contentType;
    }
}
