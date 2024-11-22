<?php

namespace App\Models\Bible;
use ReflectionClass;

/**
 * Model for the `bible_book_names` table.
 */
class BibleBookNameModel
{
    /**
     * @var int The primary key ID of the record.
     */
    private $id;

    /**
     * @var string The book ID (e.g., "GEN" for Genesis).
     */
    private $bookId;

    /**
     * @var string|null The deprecated ISO language code.
     */
    private $languageCodeIso;

    /**
     * @var string|null The main index language code.
     */
    private $languageCodeHL;

    /**
     * @var string The name of the book in the specific language.
     */
    private $name;

    /**
     * Get the ID of the record.
     *
     * @return int
     */
    public function getId(): int
    {
        return $this->id;
    }

    /**
     * Set the ID of the record.
     *
     * @param int $id
     * @return void
     */
    public function setId(int $id): void
    {
        $this->id = $id;
    }

    /**
     * Get the book ID.
     *
     * @return string
     */
    public function getBookId(): string
    {
        return $this->bookId;
    }

    /**
     * Set the book ID.
     *
     * @param string $bookId
     * @return void
     */
    public function setBookId(string $bookId): void
    {
        $this->bookId = $bookId;
    }

    /**
     * Get the deprecated ISO language code.
     *
     * @return string|null
     */
    public function getLanguageCodeIso(): ?string
    {
        return $this->languageCodeIso;
    }

    /**
     * Set the deprecated ISO language code.
     *
     * @param string|null $languageCodeIso
     * @return void
     */
    public function setLanguageCodeIso(?string $languageCodeIso): void
    {
        $this->languageCodeIso = $languageCodeIso;
    }

    /**
     * Get the main index language code.
     *
     * @return string|null
     */
    public function getLanguageCodeHL(): ?string
    {
        return $this->languageCodeHL;
    }

    /**
     * Set the main index language code.
     *
     * @param string|null $languageCodeHL
     * @return void
     */
    public function setLanguageCodeHL(?string $languageCodeHL): void
    {
        $this->languageCodeHL = $languageCodeHL;
    }

    /**
     * Get the name of the book.
     *
     * @return string
     */
    public function getName(): string
    {
        return $this->name;
    }

    /**
     * Set the name of the book.
     *
     * @param string $name
     * @return void
     */
    public function setName(string $name): void
    {
        $this->name = $name;
    }

    /**
     * Returns all properties as an associative array.
     */
    public function getProperties(): array
    {
        $reflection = new ReflectionClass($this);
        $propsArray = [];
        foreach ($reflection->getProperties() as $property) {
            $property->setAccessible(true);
            $propsArray[$property->getName()] = $property->getValue($this);
        }
        return $propsArray;
    }
}
