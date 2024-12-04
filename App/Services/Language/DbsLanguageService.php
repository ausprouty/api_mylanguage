<?php
namespace App\Services\Language;

use App\Repositories\LanguageRepository;

class DbsLanguageService {
    protected $languageRepository;

    public function __construct(LanguageRepository $languageRepository) {
        $this->languageRepository = $languageRepository;
    }

    public function processLanguageFiles() {
        $directory = ROOT_TRANSLATIONS . 'languages/';
        $scannedDirectory = array_diff(scandir($directory), ['..', '.']);
        foreach ($scannedDirectory as $languageCodeHL) {
            $bible = $this->languageRepository->getBestBibleByLanguageCodeHL($languageCodeHL);
            if (!$bible || $bible->weight != 9) {
                continue;
            }
            $format = ($bible->source === 'youversion') ? 'link' : 'text';
            $collectionCode = $bible->collectionCode;

            // Create DbsLanguageModel (or persist using repository)
            $dbs = new DbsLanguageModel($languageCodeHL, $collectionCode, $format);
        }
    }

    public function fetchLanguageOptions() {
        return $this->languageRepository->getDbsLanguageOptions();
    }
}
