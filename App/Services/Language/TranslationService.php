<?php

namespace App\Services\Language;

use App\Configuration\Config;
use App\Services\LoggerService;

/**
 * Handles translations by loading and parsing JSON files for specific
 * language codes and scopes. Provides utility functions for fetching
 * translations and handling fallback mechanisms.
 */
class TranslationService
{
    /**
     * Loads a translation file based on the language code and scope.
     * Falls back to English if the specific language file is unavailable.
     * 
     * @param string $languageCodeHL The language code (e.g., "eng00").
     * @param string $scope          The scope of the translation (e.g., "dbs").
     * 
     * @return array The translation data as an associative array.
     */
  
    public static function loadTranslation(string $languageCodeHL, string $scope, ?string $logic = null): array
    {
        // Map the scope to the corresponding filename.
        $logic_file = $logic ? "{$scope}-{$logic}.json" : "{$scope}.json";
        $default_file = "{$scope}.json";
    
        // Get the root translations directory.
        $rootTranslationsPath = Config::getDir('resources.translations');
    
        // Construct file paths for the requested language and fallback language.
        $primaryFile = "{$rootTranslationsPath}languages/{$languageCodeHL}/{$logic_file}";
        $fallbackFile1 = "{$rootTranslationsPath}languages/eng00/{$logic_file}";
        $secondaryFile = "{$rootTranslationsPath}languages/{$languageCodeHL}/{$default_file}";
        $fallbackFile2 = "{$rootTranslationsPath}languages/eng00/{$default_file}";
        $lastoptionFile = "{$rootTranslationsPath}languages/eng00/dbs";
    
        // Check each file in order and return the first found.
        $filesToCheck = [$primaryFile, $fallbackFile1, $secondaryFile, $fallbackFile2, $lastoptionFile];
   
        foreach ($filesToCheck as $file) {
            LoggerService::logInfo(
                'TranslationService',
                "$file being sought."
            );
            if (file_exists($file)) {
                return self::parseTranslationFile($file);
            }
        }
    
        // Log an error if no file was found.
        LoggerService::logError(
            'TranslationService',
            "Translation files not found for scope '$scope' in language '$languageCodeHL'."
        );
    
        return [];
    }
        
    /**
     * Parses a JSON translation file into an associative array.
     * 
     * @param string $filePath The full path to the translation file.
     * 
     * @return array The parsed translation data.
     */
    private static function parseTranslationFile(string $filePath): array
    {
        $contents = file_get_contents($filePath);
        $data = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            LoggerService::logError(
                'translation Service',
                "JSON error in file $filePath: " . json_last_error_msg()
            );
            return [];
        }

        return $data ?: [];
    }

    /**
     * Retrieves a translation for a given key from a translation array.
     * 
     * @param array  $translations The loaded translation data.
     * @param string $key          The key to translate.
     * 
     * @return string|null The translated value, or null if not found.
     */
    public static function translateKey(
        array $translations,
        string $key
    ): ?string {
        return $translations[$key] ?? null;
    }
}
