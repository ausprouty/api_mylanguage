<?php

namespace App\Controllers\BiblePassage\BibleBrain;

use App\Services\Database\DatabaseService;
use App\Models\Data\BibleBrainConnectionModel;
use App\Models\Bible\BibleModel;
use PDO;

class BibleBrainBibleController {
    private $databaseService;
    private $bibleModel;
    public $languageCodeIso;
    public $response;

    public function __construct(DatabaseService $databaseService, BibleModel $bibleModel) {
        $this->databaseService = $databaseService;
        $this->bibleModel = $bibleModel;
    }

    public function getBiblesForLanguageIso($languageCodeIso, $limit) {
        $this->languageCodeIso = $languageCodeIso;
        $url = 'https://4.dbt.io/api/bibles?language_code=' . strtoupper($languageCodeIso) . '&page=1&limit=' . $limit;
        $bibles = new BibleBrainConnectionModel($url);
        $this->response = $bibles->response->data;
    }

    public function showResponse() {
        return $this->response;
    }

    public function getFormatTypes() {
        $url = 'https://4.dbt.io/api/bibles/filesets/media/types';
        $formatTypes = new BibleBrainConnectionModel($url);
        $this->response = $formatTypes->response;
        return $formatTypes->response;
    }

    public function getDefaultBible($languageCodeIso) {
        $url = 'https://4.dbt.io/api/bibles/defaults/types?language_code=' . $languageCodeIso;
        $bible = new BibleBrainConnectionModel($url);
        $this->response = $bible->response;
    }

    public function getNextLanguageForBibleImport() {
        $query = "SELECT languageCodeIso FROM hl_languages 
                  WHERE languageCodeBibleBrain IS NOT NULL 
                  AND checkedBBBibles IS NULL LIMIT 1";

        $results = $this->databaseService->executeQuery($query);
        $this->languageCodeIso = $results->fetch(PDO::FETCH_COLUMN);
        return $this->languageCodeIso;
    }

    public function updateBibleDatabaseWithArray() {
        $count = 0;
        $audioTypes = ['audio_drama', 'audio', 'audio_stream', 'audio_drama_stream'];
        $textTypes = ['text_plain', 'text_format', 'text_usx', 'text_html', 'text_json'];
        $videoTypes = ['video_stream', 'video'];

        foreach ($this->response as $translation) {
            $this->bibleModel->setLanguageData($translation->autonym, $translation->language, $translation->iso);

            foreach ($translation->filesets as $fileset) {
                $count++;
                $this->bibleModel->resetMediaFlags();

                foreach ($fileset as $item) {
                    $this->bibleModel->determineMediaType($item->type, $audioTypes, $textTypes, $videoTypes);
                    $this->bibleModel->prepareForSave('bible_brain', $item->id, $item->volume ?? null, $item->size, $item->type);

                    $this->bibleModel->addBibleBrainBible();
                }
            }
        }

        $query = "UPDATE hl_languages 
                  SET checkedBBBibles = :today 
                  WHERE languageCodeIso = :languageCodeIso";
        $params = [':today' => date('Y-m-d'), ':languageCodeIso' => $this->languageCodeIso];
        $this->databaseService->executeQuery($query, $params);
    }
}
