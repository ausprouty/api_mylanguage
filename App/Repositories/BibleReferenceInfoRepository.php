<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use PDO;
use Exception;

class BibleReferenceInfoRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function findBookID($languageCodeHL, $bookName)
    {
        $query = 'SELECT bookId FROM bible_book_names WHERE (languageCodeHL = :languageCodeHL OR languageCodeHL = :english) AND name = :book_lookup LIMIT 1';
        $params = [
            ':languageCodeHL' => $languageCodeHL,
            ':english' => 'eng00',
            ':book_lookup' => $bookName
        ];
        return $this->fetchColumn($query, $params);
    }

    public function findBookNumber($bookID)
    {
        $query = 'SELECT bookNumber FROM bible_books WHERE bookId = :bookId LIMIT 1';
        return $this->fetchColumn($query, [':bookId' => $bookID]);
    }

    public function findTestament($bookID)
    {
        $query = 'SELECT testament FROM bible_books WHERE bookId = :bookId LIMIT 1';
        return $this->fetchColumn($query, [':bookId' => $bookID]);
    }

    public function findUversionBookID($bookID)
    {
        $query = 'SELECT uversionBookID FROM bible_books WHERE bookId = :bookId LIMIT 1';
        return $this->fetchColumn($query, [':bookId' => $bookID]);
    }

    // Utility function for fetching a single column
    private function fetchColumn($query, $params)
    {
        try {
            $results = $this->databaseService->executeQuery($query, $params);
            return $results->fetch(PDO::FETCH_COLUMN);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
}
