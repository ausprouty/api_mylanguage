<?php
declare(strict_types=1);

namespace App\Models\Bible;

use JsonSerializable;

/**
 * Model for the `bible_book_names` table.
 */
class BibleBookNameModel implements JsonSerializable
{
    private ?int $id = null;           // PK
    private string $bookId = '';       // e.g., "GEN"
    private ?string $languageCodeIso = null; // deprecated
    private ?string $languageCodeHL = null;  // primary index code
    private string $name = '';         // localized book name

    /** Hydrate from an associative array (keys must match properties). */
    public function populate(array $data): self
    {
        foreach ($data as $k => $v) {
            if (\property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
        return $this;
    }

    /** Canonical array representation. */
    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'bookId'          => $this->bookId,
            'languageCodeIso' => $this->languageCodeIso,
            'languageCodeHL'  => $this->languageCodeHL,
            'name'            => $this->name,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getBookId(): string { return $this->bookId; }
    public function getLanguageCodeIso(): ?string
    { return $this->languageCodeIso; }
    public function getLanguageCodeHL(): ?string
    { return $this->languageCodeHL; }
    public function getName(): string { return $this->name; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setBookId(string $bookId): void { $this->bookId = $bookId; }
    public function setLanguageCodeIso(?string $code): void
    { $this->languageCodeIso = $code; }
    public function setLanguageCodeHL(?string $code): void
    { $this->languageCodeHL = $code; }
    public function setName(string $name): void { $this->name = $name; }
}
