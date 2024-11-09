<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Models\Language\CountryLanguageModel;
use PDO;

class CountryLanguageRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getLanguagesWithContentForCountry($countryCode)
    {
        $query = "SELECT * FROM country_languages 
                  WHERE countryCode = :countryCode
                  AND languageCodeHL != :blank
                  GROUP BY languageCodeHL
                  ORDER BY languageNameEnglish";
        $params = [
            ':countryCode' => $countryCode,
            ':blank' => ''
        ];

        try {
            $results = $this->databaseService->executeQuery($query, $params);
            $data = $results->fetchAll(PDO::FETCH_OBJ);

            $languages = [];
            foreach ($data as $row) {
                $languages[] = new CountryLanguageModel(
                    $row->countryCode,
                    $row->languageCodeHL,
                    $row->languageNameEnglish
                );
            }
            return $languages;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}
