<?php

namespace App\Controllers;

use App\Repositories\BibleRepository;

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
}
