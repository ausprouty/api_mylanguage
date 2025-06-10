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

        $masterFile = "{$this->rootTranslationsPath}interface/{$app}/eng00.json";
        LoggerService::logInfo('loadInterfaceTranslation-125', "master file: $masterFile");

        if (!file_exists($masterFile)) {
            LoggerService::logError('TranslationService-128', "Missing English master for app '$app'");
            return [];
        }

        if ($languageCodeHL === 'eng00') {
            return self::parseTranslationFile($masterFile);
        }

        $translatedFile = "{$this->rootTranslationsPath}interface/{$app}/{$languageCodeHL}.json";
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
        //LoggerService::logInfo('createInterfaceTranslation-178', print_r($masterData, true));
        $translatedFile  = "{$this->rootTranslationsPath}interface/{$app}/{$languageCodeHL}.json";
        $googleLangCode  = $this->languageRepository->getCodeGoogleFromCodeHL($languageCodeHL);

        if (!$googleLangCode) {
            LoggerService::logError('TranslationService-183', "No Google code for $languageCodeHL");
            return $masterData;
        }
        [$translatedCore, $isComplete]  = $this->translateArrayRecursive($masterData, $googleLangCode);
        $translated = $translatedCore;
       
        $translated['language'] = [
            'EnglishName'        => $this->languageRepository->getEnglishNameForLanguageCodeHL($languageCodeHL),
            'hlCode'             => $languageCodeHL,
            'google'             => $googleLangCode,
            'translatedFrom'     => 'eng00',
            'translatedDate'     => date('c'),
            'translationComplete'=> $isComplete,
            'lastUpdated'        => $languageBlock['lastUpdated'] ?? date('c'),
        ];
        if ($isComplete){
            LoggerService::logInfo('TranslationService-200', "Writing  $translatedFile");
            file_put_contents(
                $translatedFile,
                json_encode($translated, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)
            );
        }
        else{
            LoggerService::logInfo('TranslationService-207', print_r($translated, true));
        }
        return $translated;
    }

    /**
     * Recursively translates all strings in an array using cache and Google Translate API.
     *
     * @param array $data
     * @param string $targetLang
     * @return array
     */
    private function translateArrayRecursive(array $data, string $googleLangCode): array
    {
        $translated = [];
        $complete = true;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                [$translatedValue, $isSubComplete] = $this->translateArrayRecursive($value, $googleLangCode);
                $translated[$key] = $translatedValue;
                if (!$isSubComplete) $complete = false;
            } elseif (is_string($value) && trim($value) !== '') {
                $cached = $this->translationMemoryService->get($value, $googleLangCode);
                if ($cached !== null) {
                    $translated[$key] = $cached;
                } else {
                    $complete = false;
                    $this->addToTranslationQueue($googleLangCode, $value);
                    $translated[$key] = $value; // fallback
                }
            } else {
                $translated[$key] = $value;
            }
        }

        return [$translated, $complete];
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

    private function addToTranslationQueue(string $targetLang, string $sourceText): void
{
    $query = "INSERT INTO translation_queue (target_lang, source_text)
              SELECT :target_lang, :source_text
              WHERE NOT EXISTS (
                  SELECT 1 FROM translation_queue
                  WHERE target_lang = :target_lang AND source_text = :source_text
              )";

    $params = [
        ':target_lang' => $targetLang,
        ':source_text' => $sourceText,
    ];

    $this->databaseService->executeQuery($query, $params);
}
}
