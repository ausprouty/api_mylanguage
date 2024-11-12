<?php
namespace App\Controllers\BiblePassage\BibleBrain;

use App\Services\Bible\BibleUpdateService;
use App\Repositories\LanguageRepository;
use App\Factories\BibleBrainConnectionFactory;

class BibleBrainBibleController
{
    private $bibleUpdateService;
    private $languageRepository;
    private $bibleBrainConnectionFactory;
    public $response;

    public function __construct(
        BibleUpdateService $bibleUpdateService,
        LanguageRepository $languageRepository,
        BibleBrainConnectionFactory $bibleBrainConnectionFactory
    ) {
        $this->bibleUpdateService = $bibleUpdateService;
        $this->languageRepository = $languageRepository;
        $this->bibleBrainConnectionFactory = $bibleBrainConnectionFactory;
    }

    public function getBiblesForLanguageIso($languageCodeIso, $limit)
    {
        $url = 'https://4.dbt.io/api/bibles?language_code=' . strtoupper($languageCodeIso) . '&page=1&limit=' . $limit;
        $bibles = $this->bibleBrainConnectionFactory->createModelForEndpoint($url);
        $this->response = $bibles->response->data;
    }

    public function getFormatTypes()
    {
        $url = 'https://4.dbt.io/api/bibles/filesets/media/types';
        $formatTypes = $this->bibleBrainConnectionFactory->createModelForEndpoint($url);
        $this->response = $formatTypes->response;
        return $formatTypes->response;
    }

    public function getDefaultBible($languageCodeIso)
    {
        $url = 'https://4.dbt.io/api/bibles/defaults/types?language_code=' . $languageCodeIso;
        $bible = $this->bibleBrainConnectionFactory->createModelForEndpoint($url);
        $this->response = $bible->response;
    }

    public function updateBibleDatabaseWithArray()
    {
        $this->bibleUpdateService->updateBibleDatabaseWithData($this->response, $this->languageRepository);
    }
}
