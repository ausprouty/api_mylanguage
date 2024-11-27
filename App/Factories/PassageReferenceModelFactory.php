<?php

namespace App\Factories;

use App\Models\Bible\PassageReferenceModel;
use App\Repositories\BibleReferenceRepository;

/**
 * Factory for creating and populating PassageReferenceModel instances.
 */
class PassageReferenceModelFactory
{
    private $repository;

    private $entry;
    private $languageCodeHL;
    private $languageCodeIso;
    private $bookName;
    private $bookID;
    private $uversionBookID;
    private $bookNumber;
    private $testament;
    private $chapterStart;
    private $verseStart;
    private $chapterEnd;
    private $verseEnd;

    /**
     * Constructor to initialize the repository dependency.
     */
    public function __construct(BibleReferenceRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Creates a model from an entry string and language code.
     */
    public function createFromEntry(
        string $entry,
        string $languageCodeHL = 'eng00'
    ): PassageReferenceModel {
        $model = new PassageReferenceModel();
        $this->entry = $this->checkEntrySpacing($entry);

        $this->bookName = $this->setBookName($entry);
        $this->setChapterAndVerses();
        $this->bookID = $this->repository->findBookID(
            $languageCodeHL,
            $this->bookName
        );
        $this->bookNumber = $this->repository->findBookNumber($this->bookID);
        $this->testament = $this->repository->findTestament($this->bookID);
        $this->uversionBookID = $this->repository->findUversionBookID($this->bookID);
        $model->populate([
            'entry' => $this->entry,
            'languageCodeHL' => $languageCodeHL,
            'languageCodeIso' => null,
            'bookName' => $this->bookName,
            'bookID' => $this->bookID,
            'uversionBookID' => $this->uversionBookID,
            'bookNumber' => $this->bookNumber,
            'testament' => $this->testament,
            'chapterStart' => $this->chapterStart,
            'verseStart' => $this->verseStart,
            'chapterEnd' => $this->chapterStart,
            'verseEnd' => $this->verseEnd,
        ]);

        return $model;
    }

    /**
     * Creates a model from an import object.
     */
    public function createFromImport($import): PassageReferenceModel
    {
        $model = new PassageReferenceModel();
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
    public function createFromDbtArray(array $dbtArray): PassageReferenceModel
    {
        $model = new PassageReferenceModel();
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
    private function setChapterAndVerses()
    {
        $pass = str_replace($this->bookName, '', $this->entry);
        $pass = str_replace(' ', '', $pass);
        $pass = str_replace('፡', ':', $pass); // from Amharic
        $i = strpos($pass, ':');
        if ($i == FALSE) {
            // this is the whole chapter
            $this->chapterStart = trim($pass);
            $this->verseStart = 1;
            $this->verseEnd = 999;
        } else {
            $this->chapterStart = substr($pass, 0, $i);
            $verses = substr($pass, $i + 1);
            $i = strpos($verses, '-');
            if ($i !== FALSE) {
                $this->verseStart = substr($verses, 0, $i);
                $this->verseEnd = substr($verses, $i + 1);
            } else {
                $this->verseStart = $verses;
                $this->verseEnd = $verses;
            }
        }
    }
}
