<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use PDO;

class BibleReferenceRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function findBookID($languageCodeHL, $bookName)
    {
        $query = 'SELECT bookId FROM bible_book_names 
                  WHERE (languageCodeHL = :languageCodeHL OR languageCodeHL = :english) 
                  AND name = :book_lookup LIMIT 1';
        $params = [
            ':languageCodeHL' => $languageCodeHL,
            ':english' => 'eng00',
            ':book_lookup' => $bookName
        ];
        return $this->databaseService->fetchSingleValue($query, $params);
    }

    public function findBookNumber($bookID)
    {
        $query = 'SELECT bookNumber FROM bible_books WHERE bookId = :bookId LIMIT 1';
        return $this->databaseService->fetchSingleValue($query, [':bookId' => $bookID]);
    }

    public function findTestament($bookID)
    {
        $query = 'SELECT testament FROM bible_books WHERE bookId = :bookId LIMIT 1';
        return $this->databaseService->fetchSingleValue($query, [':bookId' => $bookID]);
    }

    public function findUversionBookID($bookID)
    {
        $query = 'SELECT uversionBookID FROM bible_books WHERE bookId = :bookId LIMIT 1';
        return $this->databaseService->fetchSingleValue($query, [':bookId' => $bookID]);
    }

    public function getBookDetails($languageCodeHL, $bookName)
    {
        $query = 'SELECT bookId, bookName, bookNumber, testament, uversionBookID
              FROM bible_books
              WHERE languageCodeHL = :languageCodeHL AND name = :bookName LIMIT 1';
        $params = [
            ':languageCodeHL' => $languageCodeHL,
            ':bookName' => $bookName,
        ];
        return $this->databaseService->fetchRow($query, $params);
    }
}
