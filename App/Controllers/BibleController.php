<?php

namespace App\Controllers;

use App\Repositories\BibleRepository;
use App\Utilities\JsonResponse;


class BibleController
{
    private $bibleRepository;

    public function __construct(BibleRepository $bibleRepository)
    {
        $this->bibleRepository = $bibleRepository;
  
    }

    public function getBestBibleByLanguageCodeHL(string $languageCode)
    {
        $output =  $this->bibleRepository->findBestBibleByLanguageCodeHL($languageCode);
        return $output;
    }
    public function webGetBestBibleByLanguageCodeHL(string $languageCode)
    {
        $bibleModel =  $this->bibleRepository->findBestBibleByLanguageCodeHL($languageCode);
        $output = $bibleModel->getProperties();
        if (!is_array($output)) {
            $output = (array) $output;  // Ensure proper typecasting.
        }
        JsonResponse::success($output);
    }
}
