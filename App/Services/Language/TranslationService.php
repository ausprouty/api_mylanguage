<?php

namespace App\Services\Language;

use App\Repositories\LanguageRepository;
use App\Configuration\Config;
use App\Services\LoggerService;
use App\Services\Database\DatabaseService;
use App\Services\Language\TranslationMemoryService;

/**
 * Service responsible for loading, translating, and caching language files.
 * Provides mechanisms to handle interface and content translations,
 * including fallbacks and automatic translation using Google Translate.
 */
class TranslationService
{
    /**
     * Base path for translation resources.
     *
     * @var string
     */
    protected string $rootTranslationsPath;

    /**
     * Database service for internal use.
     *
     * @var DatabaseService
     */
    protected DatabaseService $databaseService;

    /**
     * Repository for language metadata.
     *
     * @var LanguageRepository
     */
    protected LanguageRepository $languageRepository;

    /**
     * Translation memory service for caching results.
     *
     * @var TranslationMemoryService
     */
    protected TranslationMemoryService $translationMemoryService;

    /**
     * Constructor for TranslationService.
     *
     * @param DatabaseService $databaseService
     * @param LanguageRepository $languageRepository
     * @param TranslationMemoryService $translationMemoryService
     */
    public function __construct(
        DatabaseService $databaseService,
        LanguageRepository $languageRepository,
        TranslationMemoryService $translationMemoryService
    ) {
        $this->databaseService = $databaseService;
        $this->languageRepository = $languageRepository;
        $this->rootTranslationsPath = Config::getDir('resources.translations');
        $this->translationMemoryService = $translationMemoryService;
    }

    /**
     * Loads a translation file for a specific study and language,
     * with logic for multiple fallback paths.
     *
     * @param string $languageCodeHL  HL-style language code (e.g., 'eng00')
     * @param string $scope           The translation scope or study name
     * @param string|null $logic      Optional logic suffix for filename
     * @return array                  Parsed translation array or empty if not found
     */
    public function loadStaticContentTranslation(
        string $languageCodeHL,
        string $scope,
        ?string $logic = null
    ): array {
        $logicFile     = $logic ? "{$scope}-{$logic}.json" : "{$scope}.json";
        $defaultFile   = "{$scope}.json";

        LoggerService::logInfo('TranslationService-48', "loadStaticContentTranslation");

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

        foreach ($filesToCheck as $file) {
            LoggerService::logInfo('TranslationService-98', "$file being sought.");
            if (file_exists($file)) {
                return self::parseTranslationFile($file);
            }
        }

        LoggerService::logError(
            'TranslationService-105',
            "Translation files not found for scope '$scope' in language '$languageCodeHL'."
        );

        return [];
    }

    /**
     * Loads an interface translation file for a given app and language.
     * Falls back to English and generates translation if needed.
     *
     * @param string $app             Application name
     * @param string $languageCodeHL  HL-style language code
     * @return array                  Interface translation array
     */
    public function loadInterfaceTranslation(string $app, string $languageCodeHL): array
    {
        LoggerService::logInfo('loadInterfaceTranslation-122', "line started");

        $masterFile = "{$this->rootTranslationsPath}i18n/{$app}/interface/eng00.json";
        LoggerService::logInfo('loadInterfaceTranslation-125', "master file: $masterFile");

        if (!file_exists($masterFile)) {
            LoggerService::logError('TranslationService-128', "Missing English master for app '$app'");
            return [];
        }

        if ($languageCodeHL === 'eng00') {
            return self::parseTranslationFile($masterFile);
        }

        $translatedFile = "{$this->rootTranslationsPath}i18n/{$app}/interface/{$languageCodeHL}.json";
        LoggerService::logInfo('loadInterfaceTranslation-137', "translated file: $translatedFile");

        if (file_exists($translatedFile)) {
            LoggerService::logInfo('loadInterfaceTranslation-140', "translated file exists");
            $translatedData = self::parseTranslationFile($translatedFile);
            $translatedDate = $translatedData['language']['translatedDate'] ?? null;
            $translatedFrom = $translatedData['language']['translatedFrom'] ?? null;

            $masterData = self::parseTranslationFile($masterFile);
            LoggerService::logInfo('loadInterfaceTranslation-146', print_r($masterData, true));
            $masterLastUpdated = $masterData['language']['lastUpdated'] ?? null;

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

        LoggerService::logInfo('loadInterfaceTranslation-117', "no translated file");
        $masterData = self::parseTranslationFile($masterFile);
        return $this->createInterfaceTranslation($app, $languageCodeHL, $masterData);
    }

    /**
     * Creates a new translated interface file using Google Translate,
     * based on the English master.
     *
     * @param string $app
     * @param string $languageCodeHL
     * @param array $masterData
     * @return array
     */
    private function createInterfaceTranslation(string $app, string $languageCodeHL, array $masterData): array
    {
        LoggerService::logInfo('createInterfaceTranslation-178', print_r($masterData, true));
        $translatedFile  = "{$this->rootTranslationsPath}i18n/{$app}/interface/{$languageCodeHL}.json";
        $googleLangCode  = $this->languageRepository->getCodeGoogleFromCodeHL($languageCodeHL);

        if (!$googleLangCode) {
            LoggerService::logError('TranslationService-183', "No Google code for $languageCodeHL");
            return $masterData;
        }

        $languageBlock = $masterData['language'] ?? [];
        unset($masterData['language']);
        LoggerService::logInfo('createInterfaceTranslation-190', print_r($languageBlock, true));
        $translated = $this->translateArrayRecursive($masterData, $googleLangCode);
        LoggerService::logInfo('createInterfaceTranslation-191', print_r($translated, true));
        if (!$this->isTranslationValid($translated)) {
            LoggerService::logError('TranslationService-192', "Empty or failed translation for $languageCodeHL");
            return $masterData;
        }

        $translated['language'] = [
            'EnglishName'     => $this->languageRepository->getEnglishNameForLanguageCodeHL($languageCodeHL),
            'hlCode'          => $languageCodeHL,
            'google'          => $googleLangCode,
            'translatedFrom'  => 'eng00',
            'translatedDate'  => date('c'),
            'lastUpdated'     => $languageBlock['lastUpdated'] ?? date('c'),
        ];
        LoggerService::logInfo('TranslationService-204', "Writing  $translatedFile");

        file_put_contents(
            $translatedFile,
            json_encode($translated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
        );

        return $translated;
    }

    /**
     * Recursively translates all strings in an array using cache and Google Translate API.
     *
     * @param array $data
     * @param string $targetLang
     * @return array
     */
    private function translateArrayRecursive(array $data, string $targetLang): array
    {
        LoggerService::logInfo('translateArrayRecursive-225', print_r($data, true));
        LoggerService::logInfo('translateArrayRecursive-226', "$targetLang");
        foreach ($data as $key => $value) {
            if (is_array($value)) {
                LoggerService::logInfo('translateArrayRecursive-229', print_r($value,true));
                $data[$key] = $this->translateArrayRecursive($value, $targetLang);
            } elseif (is_string($value) && trim($value) !== '') {
                LoggerService::logInfo('translateArrayRecursive-232', "$key -- $value");
                $cachedTranslation = $this->translationMemoryService->get($value, $targetLang);
                LoggerService::logInfo('translateArrayRecursive-234', "$cachedTranslation");
                if ($cachedTranslation !== null) {
                    $data[$key] = $cachedTranslation;
                } else {
                    LoggerService::logInfo('translateArrayRecursive-233', "print_r($value)");
                    $translated = $this->googleTranslate($value, $targetLang);
                    if ($translated) {
                        $this->translationMemoryService->save($value, $targetLang, $translated);
                    }
                    $data[$key] = $translated;
                    usleep(100000);
                }
            }
        }

        return $data;
    }

    /**
     * Validates if translated data contains any non-empty strings.
     *
     * @param array $translatedData
     * @return bool
     */
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
     * Translates a single string using Google Translate API.
     *
     * @param string $text
     * @param string $targetLanguage
     * @param string $sourceLanguage
     * @return string
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
     *
     * @param string $filePath
     * @return array
     */
    private static function parseTranslationFile(string $filePath): array
    {
        $contents = file_get_contents($filePath);
        LoggerService::logInfo('parseTranslationFile-323', "$filePath");
        LoggerService::logInfo('parseTranslationFile-324', "print_r($contents)");
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
     * Retrieves a translated value from an array using the given key.
     *
     * @param array $translations
     * @param string $key
     * @return string|null
     */
    public static function translateKey(array $translations, string $key): ?string
    {
        return $translations[$key] ?? null;
    }
}
