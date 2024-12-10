<?php

namespace App\Services\Language;

use App\Configuration\Config;

class TranslationService
{
    /**
     * @var array The translation data loaded from the file.
     */
    private $translation;

    /**
     * Constructor to initialize the translation service.
     * 
     * @param string $languageCodeHL The language code (e.g., "eng00").
     * @param string $scope          The scope of the translation (e.g., "dbs").
     */
    public function __construct(string $languageCodeHL, string $scope)
    {
        $this->translation = $this->loadTranslationFile($languageCodeHL, $scope);
    }

    /**
     * Loads a translation file based on the language code and scope.
     * Falls back to English if the specific language file is unavailable.
     * 
     * @param string $languageCodeHL The language code.
     * @param string $scope          The scope of the translation.
     * 
     * @return array The translation data as an associative array.
     */
    private function loadTranslationFile(string $languageCodeHL, string $scope): array
    {
        // Map the scope to the corresponding filename.
        $filename = match ($scope) {
            'dbs' => 'dbs.json',
            'lead' => 'leadership.json',
            'life' => 'life.json',
            'video' => 'video.json',
            default => null,
        };

        if ($filename === null) {
            trigger_error("Invalid translation scope: $scope", E_USER_WARNING);
            return [];
        }

        $rootTranslationsPath = Config::getDir('paths.resources.translations');

        // Construct file paths for the requested language and fallback language.
        $file = $rootTranslationsPath . "languages/$languageCodeHL/$filename";
        $fallbackFile = $rootTranslationsPath . "languages/eng00/$filename";

        // Attempt to load the translation file or fallback file.
        if (file_exists($file)) {
            return $this->parseTranslationFile($file);
        }

        if (file_exists($fallbackFile)) {
            return $this->parseTranslationFile($fallbackFile);
        }

        // Log an error and return an empty array if neither file exists.
        error_log("Translation files not found for scope '$scope' in $languageCodeHL.");
        return [];
    }

    /**
     * Parses a JSON translation file into an associative array.
     * 
     * @param string $filePath The full path to the translation file.
     * 
     * @return array The parsed translation data.
     */
    private function parseTranslationFile(string $filePath): array
    {
        $contents = file_get_contents($filePath);
        $data = json_decode($contents, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            error_log("JSON error in file $filePath: " . json_last_error_msg());
            return [];
        }

        return $data ?: [];
    }

    /**
     * Retrieves the loaded translation data.
     * 
     * @return array The translation data as an associative array.
     */
    public function getTranslationData(): array
    {
        return $this->translation;
    }

    /**
     * Translates a key into its corresponding value.
     * 
     * @param string $key The key to translate.
     * 
     * @return string|null The translated value, or null if the key is not found.
     */
    public function translateTwigKey(string $key): ?string
    {
        return $this->translation[$key] ?? null;
    }
}
