<?php
declare(strict_types=1);

namespace App\Models\Language;

use JsonSerializable;

class DbsLanguageModel implements JsonSerializable
{
    private ?string $languageCodeHL = null;
    private ?string $collectionCode = null;
    private ?string $format = null;

    /**
     * Populate from an associative array. Keys must match properties.
     */
    public function populate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (\property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Canonical array representation for logging/JSON/etc.
     */
    public function toArray(): array
    {
        return [
            'languageCodeHL' => $this->languageCodeHL,
            'collectionCode' => $this->collectionCode,
            'format'         => $this->format,
        ];
    }

    /**
     * JsonSerializable implementation delegates to toArray().
     */
    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // Getters
    public function getLanguageCodeHL(): ?string
    {
        return $this->languageCodeHL;
    }

    public function getCollectionCode(): ?string
    {
        return $this->collectionCode;
    }

    public function getFormat(): ?string
    {
        return $this->format;
    }

    // Setters
    public function setLanguageCodeHL(?string $languageCodeHL): void
    {
        $this->languageCodeHL = $languageCodeHL;
    }

    public function setCollectionCode(?string $collectionCode): void
    {
        $this->collectionCode = $collectionCode;
    }

    public function setFormat(?string $format): void
    {
        $this->format = $format;
    }
}
