<?php

namespace App\Factories;

use App\Models\Bible\BibleReferenceInfoModel;
use App\Repositories\BibleReferenceInfoRepository;

/**
 * Factory for creating and populating BibleReferenceInfoModel instances.
 */
class BibleReferenceInfoModelFactory
{
    private $repository;

    /**
     * Constructor to initialize the repository dependency.
     */
    public function __construct(BibleReferenceInfoRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Creates a model from an entry string and language code.
     */
    public function createFromEntry(
        string $entry,
        string $languageCodeHL = 'eng00'
    ): BibleReferenceInfoModel {
        $model = new BibleReferenceInfoModel();
        $model->populate([
            'entry' => $this->checkEntrySpacing($entry),
            'languageCodeHL' => $languageCodeHL,
        ]);

        $bookDetails = $this->repository->getBookDetails($languageCodeHL, $entry);
        if ($bookDetails) {
            $model->populate($bookDetails); // Populate the model with the new data
        }

        return $model;
    }

    /**
     * Creates a model from an import object.
     */
    public function createFromImport($import): BibleReferenceInfoModel
    {
        $model = new BibleReferenceInfoModel();
        $model->populate((array) $import);
        return $model;
    }


    /**
     * Checks and adjusts entry spacing for consistency.
     */
    private function checkEntrySpacing(string $entry): string
    {
        $entry = trim($entry);
        if (strpos($entry, ' ') === false) {
            $firstNumber = mb_strlen($entry);
            for ($i = 0; $i <= 9; $i++) {
                $pos = mb_strpos($entry, (string) $i);
                if ($pos !== false && $pos < $firstNumber) {
                    $firstNumber = $pos;
                }
            }
            $book = mb_substr($entry, 0, $firstNumber);
            $chapter = mb_substr($entry, $firstNumber);
            $entry = $book . ' ' . $chapter;
        }
        return $entry;
    }

    /**
     * Creates a model from a DBT array.
     */
    public function createFromDbtArray(array $dbtArray): BibleReferenceInfoModel
    {
        $model = new BibleReferenceInfoModel();
        $model->populate([
            'entry' => $this->checkEntrySpacing($dbtArray['entry']),
            'bookName' => $this->setBookName($dbtArray['entry']),
            'bookID' => $dbtArray['bookId'],
            'testament' => $dbtArray['collection_code'],
            'chapterStart' => $dbtArray['chapterId'],
            'verseStart' => $dbtArray['verseStart'],
            'chapterEnd' => null,
            'verseEnd' => $dbtArray['verseEnd'],
        ]);
        return $model;
    }

    
    /**
     * Determines the book name from an entry.
     */
    private function setBookName(string $entry): string
    {
        $parts = explode(' ', $entry);
        $book = $parts[0];
        if (in_array($book, ['1', '2', '3'], true) && isset($parts[1])) {
            $book .= ' ' . $parts[1];
        }
        if ($book === 'Psalm') {
            $book = 'Psalms';
        }
        return $book;
    }
}
