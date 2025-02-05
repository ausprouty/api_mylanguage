<?php

namespace App\Controllers\Language;

use App\Repositories\DbsLanguageRepository;
use App\Utilities\JsonResponse;
use Exception;

class DbsLanguageReadController {
    protected $dbsLanguageRepository;

    public function __construct(DbsLanguageRepository $dbsLanguageRepository) {
        $this->dbsLanguageRepository = $dbsLanguageRepository;
    }

    public function getLanguagesWithCompleteBible() {
        return $this->dbsLanguageRepository->getLanguagesWithCompleteBible();
    }

    public function webGetLanguagesWithCompleteBible() {
        $output = $this->getLanguagesWithCompleteBible();
        JsonResponse::success($output);
    }
}


