<?php

namespace App\Services\Language;

use App\Repositories\LanguageRepository;
use App\Configuration\Config;
use App\Services\LoggerService;
use App\Services\Database\DatabaseService;

/**
 * Handles translations by loading and parsing JSON files for specific
 * language codes and scopes. Provides utility functions for fetching
 * translations and handling fallback mechanisms.
 */
class TranslationService
{
    protected string $rootTranslationsPath;
    protected DatabaseService $databaseService;
    protected LanguageRepository $languageRepository;

    /**
     * Constructor for TranslationService.
     */
    public function __construct(
        DatabaseService $databaseService,
        LanguageRepository $languageRepository
    ) {
        $this->databaseService = $databaseService;
        $this->languageRepository = $languageRepository;
        $this->rootTranslationsPath = Config::getDir('resources.translations');
    }

    /**
     * Loads a translation for a specific scope and language, with fallback logic.
     */
    public function loadCommonContentTranslation(
        string $languageCodeHL,
        string $scope,
        ?string $logic = null
    ): array {
        $logicFile     = $logic ? "{$scope}-{$logic}.json" : "{$scope}.json";
        $defaultFile   = "{$scope}.json";

        // Build potential file paths
        $primaryFile    = "{$this->rootTranslationsPath}languages/{$languageCodeHL}/{$logicFile}";
        $fallbackFile1  = "{$this->rootTranslationsPath}languages/eng00/{$logicFile}";
        $secondaryFile  = "{$this->rootTranslationsPath}languages/{$languageCodeHL}/{$defaultFile}";
        $fallbackFile2  = "{$this->rootTranslationsPath}languages/eng00/{$defaultFile}";
        $lastOptionFile = "{$this->rootTranslationsPath}languages/eng00/dbs";

        $filesToCheck = [
            $primaryFile,
            $fallbackFile1,
            $secondaryFile,
            $fallbackFile2,
            $lastOptionFile
        ];

        // Try each file in order
        foreach ($filesToCheck as $file) {
            LoggerService::logInfo('TranslationService', "$file being sought.");
            if (file_exists($file)) {
                return self::parseTranslationFile($file);
            }
        }

        // Log an error if all fallbacks fail
        LoggerService::logError(
            'TranslationService',
            "Translation files not found for scope '$scope' in language '$languageCodeHL'."
        );

        return [];
    }

    /**
     * Loads an interface translation file for an app and language.
     */
    public function loadInterfaceTranslation(string $app, string $languageCodeHL): array
    {
        $fileToCheck = "{$this->rootTranslationsPath}languages/i18n/{$app}/interface/{$languageCodeHL}.json";

        if (file_exists($fileToCheck)) {
            // File already exists — return its contents
            return self::parseTranslationFile($fileToCheck);
        }

        // Otherwise, create and return the translation
        return $this->createInterfaceTranslation($app, $languageCodeHL);
    }

    /**
     * Creates a translated interface file using Google Translate.
     */
    private function createInterfaceTranslation(string $app, string $languageCodeHL): array
    {
        $masterFile = "{$this->rootTranslationsPath}i18n/project/{$app}/interface/eng00.json";
         LoggerService::logInfo('TranslationService-97', "$masterFile being sought.");
        if (!file_exists($masterFile)) {
            LoggerService::logError('TranslationService-100', "Master file not found for App '$app'.");
            return [];
        }

        $masterData = json_decode(file_get_contents($masterFile), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            LoggerService::logError(
                'TranslationService-107',
                'JSON error in master file: ' . json_last_error_msg()
            );
            return [];
        }

        // Get the Google translation code for the target language
        $googleLangCode = $this->languageRepository->getCodeGoogleFromCodeHL($languageCodeHL);

        if (!$googleLangCode) {
            LoggerService::logError(
                'TranslationService-118',
                "No Google language code found for HL code: $languageCodeHL"
            );
            return [];
        }

        // Translate the entire structure recursively
        return $this->translateArrayRecursive($masterData, $googleLangCode);
    }

    /**
     * Recursively translates all strings in a nested array using Google Translate.
     */
    private function translateArrayRecursive(array $data, string $targetLang): array
    {
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                $data[$key] = $this->translateArrayRecursive($value, $targetLang);
            } elseif (is_string($value)) {
                $data[$key] = $this->googleTranslate($value, $targetLang);
                usleep(100000); // Sleep 100ms to respect API rate limits
            }
        }
        return $data;
    }

    /**
     * Uses the Google Translate API to translate a single string.
     */
    private function googleTranslate(
        string $text,
        string $targetLanguage,
        string $sourceLanguage = 'en'
    ): string {
        $apiKey = Config::get('api.google_api_key');
        $url = 'https://translation.googleapis.com/language/translate/v2';

        $postData = [
            'q' => $text,
            'source' => $sourceLanguage,
            'target' => $targetLanguage,
            'format' => 'text',
            'key' => $apiKey
        ];

        $options = [
            'http' => [
                'header'  => "Content-Type: application/json\r\n",
                'method'  => 'POST',
                'content' => json_encode($postData)
            ]
        ];

        $context = stream_context_create($options);
        $result = file_get_contents($url, false, $context);

        if ($result === false) {
            throw new \Exception("Translation API call failed");
        }

        $data = json_decode($result, true);
        return $data['data']['translations'][0]['translatedText'] ?? '';
    }

    /**
     * Parses a JSON file from disk into an associative array.
     */
    private static function parseTranslationFile(string $filePath): array
    {
        $contents = file_get_contents($filePath);
        $data = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            LoggerService::logError(
                'TranslationService',
                "JSON error in file $filePath: " . json_last_error_msg()
            );
            return [];
        }

        return $data ?: [];
    }

    /**
     * Retrieves a translated value from a translation array.
     */
    public static function translateKey(array $translations, string $key): ?string
    {
        return $translations[$key] ?? null;
    }
}
