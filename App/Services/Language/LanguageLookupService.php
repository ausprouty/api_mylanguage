<?php
namespace App\Services\Language;

use App\Repositories\BibleGatewayRepository;

class LanguageLookupService
{
    private $repository;

    public function __construct(BibleGatewayRepository $repository)
    {
        $this->repository = $repository;
    }

    public function findOrInsertLanguageCode($code, $languageName)
    {
        $isoCode = $this->repository->tryLanguageCodeIso($code)
            ?? $this->repository->tryLanguageCodeGoogle($code)
            ?? $this->repository->tryLanguageCodeBrowser($code);

        if (!$isoCode) {
            $isoCode = $this->repository->insertNewLanguageCode($code, $languageName);
        }
        
        return $isoCode;
    }
}
