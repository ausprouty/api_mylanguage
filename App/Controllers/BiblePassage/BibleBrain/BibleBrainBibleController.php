<?php

namespace App\Controllers\BiblePassage\BibleBrain;

use App\Services\BibleUpdateService;
use App\Repositories\LanguageRepository;
use App\Models\Data\BibleBrainConnectionModel;

class BibleBrainBibleController
{
    private $bibleUpdateService;
    private $languageRepository;
    public $response;

    public function __construct(BibleUpdateService $bibleUpdateService, LanguageRepository $languageRepository)
    {
        $this->bibleUpdateService = $bibleUpdateService;
        $this->languageRepository = $languageRepository;
    }

    public function getBiblesForLanguageIso($languageCodeIso, $limit)
    {
        $url = 'https://4.dbt.io/api/bibles?language_code=' . strtoupper($languageCodeIso) . '&page=1&limit=' . $limit;
        $bibles = new BibleBrainConnectionModel($url);
        $this->response = $bibles->response->data;
    }

    public function showResponse()
    {
        return $this->response;
    }

    public function getFormatTypes()
    {
        $url = 'https://4.dbt.io/api/bibles/filesets/media/types';
        $formatTypes = new BibleBrainConnectionModel($url);
        $this->response = $formatTypes->response;
        return $formatTypes->response;
    }

    public function getDefaultBible($languageCodeIso)
    {
        $url = 'https://4.dbt.io/api/bibles/defaults/types?language_code=' . $languageCodeIso;
        $bible = new BibleBrainConnectionModel($url);
        $this->response = $bible->response;
    }

    public function updateBibleDatabaseWithArray()
    {
        $this->bibleUpdateService->updateBibleDatabaseWithData($this->response, $this->languageRepository);
    }
}
