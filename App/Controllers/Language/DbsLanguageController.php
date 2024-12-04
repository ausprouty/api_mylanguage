<?php

namespace App\Controllers\Language;

use App\Services\Language\DbsLanguageService;
use Exception;

class DbsLanguageController {
    protected $languageService;

    public function __construct(DbsLanguageService $languageService) {
        $this->languageService = $languageService;
    }

    public function updateDatabase() {
        try {
            $this->languageService->processLanguageFiles();
        } catch (Exception $e) {
            // Handle and log exception
            echo "Error: " . $e->getMessage();
        }
    }

    public function getLanguageOptions() {
        return $this->languageService->fetchLanguageOptions();
    }
}
