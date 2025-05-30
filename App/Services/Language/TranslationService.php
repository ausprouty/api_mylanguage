<?php

namespace App\Services\Language;

use App\Repositories\LanguageRepository;
use App\Configuration\Config;
use App\Services\LoggerService;
use App\Services\Database\DatabaseService;
use App\Services\Language\TranslationMemoryService;

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
    protected TranslationMemoryService $translationMemoryService;

    /**
     * Constructor for TranslationService.
     */
    public function __construct(
        DatabaseService $databaseService,
        LanguageRepository $languageRepository,
        TranslationMemoryService $translationMemoryService
    ) {
        $this->databaseService = $databaseService;
        $this->languageRepository = $languageRepository;
        $this->rootTranslationsPath = Config::getDir('resources.translations');
        $this->$translationMemoryService = $translationMemoryService;
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
   LoggerService::logInfo('loadInterfaceTranslation-81', "line started");
  
    $masterFile     = "{$this->rootTranslationsPath}i18n/{$app}/interface/eng00.json";
    LoggerService::logInfo('loadInterfaceTranslation-84', "master file: $masterFile");
    if (!file_exists($masterFile)) {
        LoggerService::logError('TranslationService', "Missing English master for app '$app'");
        return [];
    }
    // Shortcut: if requesting the English master, just load and return it
    if ($languageCodeHL === 'eng00') {
         return self::parseTranslationFile($masterFile);
    }
    // for all other languages
    $translatedFile = "{$this->rootTranslationsPath}i18n/{$app}/interface/{$languageCodeHL}.json";
    LoggerService::logInfo('loadInterfaceTranslation-95', "translated file: $translatedFile");
    if (file_exists($translatedFile)) {
        LoggerService::logInfo('loadInterfaceTranslation-97', "translated file exists");
        $translatedData = self::parseTranslationFile($translatedFile);
        LoggerService::logInfo('loadInterfaceTranslation-99',  print_r($translatedData, true));
        $translatedDate = $translatedData['language']['translatedDate'] ?? null;
        $translatedFrom = $translatedData['language']['translatedFrom'] ?? null;

        $masterData = self::parseTranslationFile($masterFile);
        LoggerService::logInfo('loadInterfaceTranslation-104',  print_r($masterData, true));
        $masterLastUpdated = $masterData['language']['lastUpdated'] ?? null;
        // do I need to re-create (esp if the English was updated since this translation was created)
        if (
            $translatedFrom !== 'eng00' ||
            !$masterLastUpdated ||
            !$translatedDate ||
            $masterLastUpdated > $translatedDate
        ) {
            LoggerService::logInfo('TranslationService', "Master updated since translation — regenerating $languageCodeHL");
            return $this->createInterfaceTranslation($app, $languageCodeHL, $masterData);
        }
        LoggerService::logInfo('loadInterfaceTranslation-116', "returning original data");
        return $translatedData;
    }
    // No translated file — create new
     LoggerService::logInfo('loadInterfaceTranslation-117', "no translated file");
    $masterData = self::parseTranslationFile($masterFile);
    return $this->createInterfaceTranslation($app, $languageCodeHL, $masterData);
}


    /**
     * Creates a translated interface file using Google Translate.
     */
    private function createInterfaceTranslation(string $app, string $languageCodeHL, array $masterData): array
{
    $translatedFile  = "{$this->rootTranslationsPath}i18n/{$app}/interface/{$languageCodeHL}.json";
    $googleLangCode  = $this->languageRepository->getCodeGoogleFromCodeHL($languageCodeHL);

    if (!$googleLangCode) {
        LoggerService::logError('TranslationService', "No Google code for $languageCodeHL");
        return $masterData;
    }

    // Extract language block and remove it from translation
    $languageBlock = $masterData['language'] ?? [];
    unset($masterData['language']);

    // Translate the structure
    $translated = $this->translateArrayRecursive($masterData, $googleLangCode);

    // Check if any translations succeeded
    if (!$this->isTranslationValid($translated)) {
        LoggerService::logError('TranslationService', "Empty or failed translation for $languageCodeHL");
        return $masterData;
    }

    // Add metadata back in
    $translated['language'] = [
        'EnglishName'     => $this->languageRepository->getEnglishNameForLanguageCodeHL($languageCodeHL),
        'hlCode'          => $languageCodeHL,
        'google'          => $googleLangCode,
        'translatedFrom'  => 'eng00',
        'translatedDate'  => date('c'),
        'lastUpdated'     => $languageBlock['lastUpdated'] ?? date('c'),
    ];

    // Save to file
    file_put_contents(
        $translatedFile,
        json_encode($translated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
    );

    return $translated;
}



    /**
     * Recursively translates all strings in a nested array using Google Translate.
     */
    private function translateArrayRecursive(array $data, string $targetLang): array
{
    foreach ($data as $key => $value) {
        if (is_array($value)) {
            $data[$key] = $this->translateArrayRecursive($value, $targetLang);
        } elseif (is_string($value) && trim($value) !== '') {
            // 1. Try cache first
            $cachedTranslation = $this->translationMemory->get($value, $targetLang);

            if ($cachedTranslation !== null) {
                $data[$key] = $cachedTranslation;
            } else {
                // 2. Call Google API
                $translated = $this->googleTranslate($value, $targetLang);

                // 3. Save to memory
                if ($translated) {
                    $this->translationMemory->save($value, $targetLang, $translated);
                }

                $data[$key] = $translated;
                usleep(100000); // Be gentle with the API
            }
        }
    }

    return $data;
}


    //to make sure translation is valid
    private function isTranslationValid(array $translatedData): bool
    {
        $iterator = new \RecursiveIteratorIterator(new \RecursiveArrayIterator($translatedData));
        foreach ($iterator as $value) {
            if (is_string($value) && trim($value) !== '') {
                return true;
            }
        }
        return false;
    }

    /**
     * Uses the Google Translate API to translate a single string.
     */
    private function googleTranslate(string $text, string $targetLanguage, string $sourceLanguage = 'en'): string
{
    $apiKey = Config::get('api.google_api_key');

    if (!$apiKey) {
        LoggerService::logError('TranslationService', 'Missing Google API key.');
        return '';
    }

    $url = 'https://translation.googleapis.com/language/translate/v2?key=' . $apiKey;

    $postData = [
        'q' => $text,
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

    LoggerService::logInfo('TranslationService', "HTTP Code: $httpCode");
    LoggerService::logInfo('TranslationService', "Result: $response");

    if ($response === false || $httpCode !== 200) {
        LoggerService::logError('TranslationService', "cURL error: $error");
        return '';
    }

    $data = json_decode($response, true);

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
