<?php

namespace App\Services\Language;

class TranslationService
{
    private $translation;

    public function __construct(string $languageCodeHL, string $scope)
    {
        $this->translation = $this->loadTranslationFile($languageCodeHL, $scope);
    }

    private function loadTranslationFile(string $languageCodeHL, string $scope): array
    {
        switch ($scope) {
            case 'dbs':
                $filename = 'dbs.json';
                break;
            case 'leadership':
                $filename = 'leadership.json';
                break;
            case 'life':
                $filename = 'life.json';
                break;
            case 'video':
                $filename = 'video.json';
                break;
            default:
                return [];
        }

        // Attempt to load the specified language file
        $file = ROOT_TRANSLATIONS . 'languages/' . $languageCodeHL . '/' . $filename;
        if (file_exists($file)) {
            return json_decode(file_get_contents($file), true) ?? [];
        }

        // Fallback to English if the file doesn't exist
        $fallbackFile = ROOT_TRANSLATIONS . 'languages/eng00/' . $filename;
        if (file_exists($fallbackFile)) {
            return json_decode(file_get_contents($fallbackFile), true) ?? [];
        }

        // Log an error if neither file exists
        $message = $fallbackFile . " not found";
        trigger_error($message, E_USER_ERROR);
        return [];
    }

    public function getTranslationData(): array
    {
        return $this->translation;
    }

    public function translateText(string $text): ?string
    {
        return $this->translation[$text] ?? null;
    }
}
