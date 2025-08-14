<?php

namespace App\Services\Language;

use App\Configuration\Config;
use App\Repositories\LanguageRepository;
use App\Services\Database\DatabaseService;
use App\Services\LoggerService;
use App\Services\Language\TranslationMemoryService;

/**
 * Service for loading and translating JSON-based language files dynamically.
 * Automatically translates non-English content using cached memory or queues for future translation.
 */
class TranslationService
{
    protected string $rootTranslationsPath;
    protected DatabaseService $databaseService;
    protected LanguageRepository $languageRepository;
    protected TranslationMemoryService $translationMemoryService;

    public function __construct(
        DatabaseService $databaseService,
        LanguageRepository $languageRepository,
        TranslationMemoryService $translationMemoryService
    ) {
        $this->rootTranslationsPath = Config::getDir('resources.translations');
        $this->databaseService = $databaseService;
        $this->languageRepository = $languageRepository;
        $this->translationMemoryService = $translationMemoryService;
    }

    /**
     * Returns translated content (or master English if requested) for a given type/sourceKey.
     * 
     * @param string $type One of:
     *                     - 'commonContent': shared translations across studies
     *                     - 'interface': UI elements for a specific app (e.g., 'beta')
     *                     - other folders under `resources.translations`
     *  @param string $type Specifies the internal scope:
     *                     - `commonContent`, 
     *                     - ` interface`
     * @param string $sourceKey Specifies the internal scope:
     *                     - For `commonContent`, this could be:
     *                         'dbs', 'life', 'grow', 'bible', 'video', etc.
     *                     - For `interface`, this is typically:
     *                         an app name like 'beta', 'admin', etc.
     *                     - Must correspond to a subdirectory inside the type folder.
     * 
     * @param string $languageCodeHL The HL-style language code (e.g., 'eng00', 'urd00')
     * 
     * @return array The translated JSON as a PHP associative array.
     */
    public function getTranslatedContent(string $type, string $sourceKey, string $languageCodeHL): array
    {
        // Load the English master file
        $masterFile = "{$this->rootTranslationsPath}{$type}/{$sourceKey}/eng00.json";
        if (!file_exists($masterFile)) {
            LoggerService::logError("TranslationService", "Missing master file: " .  $masterFile);
            return [' Missing master file: $masterFile'];
        }

        $masterData = self::parseJsonFile($masterFile);

        // Return English directly if requested
        if ($languageCodeHL === 'eng00') {
            return $masterData;
        }
        //unset English language metadata
        unset($masterData['language']);
        // Get Google Translate code for the language
        $googleCode = $this->languageRepository->getCodeGoogleFromCodeHL($languageCodeHL);
        if (!$googleCode) {
            LoggerService::logError("TranslationService", "Missing Google code for $languageCodeHL");
            return $masterData;
        }

        // Perform recursive translation
        [$translatedData, $complete] = $this->translateArrayRecursive($masterData, $googleCode);

        // Add metadata to the translated output
        $translatedData['language'] = [
            'hlCode' => $languageCodeHL,
            'google' => $googleCode,
            'translatedFrom' => 'eng00',
            'translatedDate' => date('c'),
            'translationComplete' => $complete,
            'lastUpdated' => $masterData['language']['lastUpdated'] ?? date('c'),
        ];
        if ($complete === false) {
            $cronKey = bin2hex(random_bytes(16)); // 32-char secure token
            $this->databaseService->executeQuery(
                "INSERT INTO cron_tokens (token) VALUES (:token)",
                [':token' => $cronKey]
            );
            $translatedData['language']['cronKey'] = $cronKey;
        }
        return $translatedData;
    }

    /**
     * Recursively translates each string in the array using cached memory or queues it for future translation.
     *
     * @param array $data
     * @param string $targetLang Google Translate target language code (e.g., 'ur')
     * @return array [translated array, isComplete flag]
     */
    private function translateArrayRecursive(array $data, string $targetLang): array
    {
        $translated = [];
        $complete = true;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                [$subTranslated, $isSubComplete] = $this->translateArrayRecursive($value, $targetLang);
                $translated[$key] = $subTranslated;
                if (!$isSubComplete) $complete = false;
            } elseif (is_string($value) && trim($value) !== '') {
                // get value stored in translation_memory
                $cached = $this->translationMemoryService->get($value, $targetLang);
                if ($cached !== null) {
                    $translated[$key] = $cached;
                } else {
                    // indicate complete is false and add to translation_queue
                    // translation_queue is emptied by a cron job
                    // App\Cron\TranslationQueProcessor.php
                    $complete = false;
                    $this->addToTranslationQueue($targetLang, $value);
                    $translated[$key] = $value; // fallback to English
                }
            } else {
                $translated[$key] = $value;
            }
        }

        return [$translated, $complete];
    }

    /**
     * Parses a JSON file into an associative array.
     *
     * @param string $filePath
     * @return array
     */
    private static function parseJsonFile(string $filePath): array
    {
        $content = file_get_contents($filePath);
        $data = json_decode($content, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            LoggerService::logError("TranslationService", "JSON error in $filePath: " . json_last_error_msg());
            return [];
        }

        return $data ?? [];
    }

    /**
     * Queues a missing string for later translation using Google Translate.
     *
     * @param string $targetLang
     * @param string $sourceText
     * @return void
     */
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
