<?php

namespace App\Services\Language;

use App\Configuration\Config;
use App\Services\LoggerService;

class TranslationBatchService
{
    protected string $apiKey;

    public function __construct()
    {
        $this->apiKey = Config::get('api.google_api_key');

        if (!$this->apiKey) {
            LoggerService::logError('TranslationBatchService', 'Missing Google API key.');
            throw new \RuntimeException('Google API key is required.');
        }
    }

    /**
     * Sends a batch of texts to Google Translate API.
     *
     * @param array $texts The English texts to translate.
     * @param string $targetLanguage The target language code.
     * @param string $sourceLanguage The source language code (default: 'en').
     * @return array Array of translated strings in the same order.
     */
    public function translateBatch(array $texts, string $targetLanguage, string $sourceLanguage = 'en'): array
    {
        if (empty($texts)) return [];

        $url = 'https://translation.googleapis.com/language/translate/v2?key=' . $this->apiKey;

        $postData = [
            'q' => $texts,
            'source' => $sourceLanguage,
            'target' => $targetLanguage,
            'format' => 'text'
        ];

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: MyLanguageApp/1.0'
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        LoggerService::logInfo('TranslationBatchService', "HTTP Code: $httpCode");
        LoggerService::logInfo('TranslationBatchService', "Response: $response");

        if ($response === false || $httpCode !== 200) {
            LoggerService::logError('TranslationBatchService', "cURL error: $error");
            return array_fill(0, count($texts), '');
        }

        $data = json_decode($response, true);

        return array_map(
            fn($item) => $item['translatedText'] ?? '',
            $data['data']['translations'] ?? []
        );
    }
}
