<?php
declare(strict_types=1);

namespace App\Models\Tract;

use JsonSerializable;

class TractModel implements JsonSerializable
{
    private ?int $id = null;
    private ?string $languageCodeHL1 = null;
    private ?string $languageCodeHL2 = null;
    private string $name = '';
    private string $webpage = '';
    private ?bool $valid = null;
    private ?string $validMessage = null;

    public function populate(array $data): self
    {
        foreach ($data as $k => $v) {
            if (\property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
        return $this;
    }

    public function toArray(): array
    {
        return [
            'id'              => $this->id,
            'languageCodeHL1' => $this->languageCodeHL1,
            'languageCodeHL2' => $this->languageCodeHL2,
            'name'            => $this->name,
            'webpage'         => $this->webpage,
            'valid'           => $this->valid,
            'validMessage'    => $this->validMessage,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getLanguageCodeHL1(): ?string { return $this->languageCodeHL1; }
    public function getLanguageCodeHL2(): ?string { return $this->languageCodeHL2; }
    public function getName(): string { return $this->name; }
    public function getWebpage(): string { return $this->webpage; }
    public function isValid(): ?bool { return $this->valid; }
    public function getValidMessage(): ?string { return $this->validMessage; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setLanguageCodeHL1(?string $code): void
    { $this->languageCodeHL1 = $code; }
    public function setLanguageCodeHL2(?string $code): void
    { $this->languageCodeHL2 = $code; }
    public function setName(string $name): void { $this->name = $name; }
    public function setWebpage(string $url): void { $this->webpage = $url; }
    public function setValid(?bool $valid): void { $this->valid = $valid; }
    public function setValidMessage(?string $msg): void
    { $this->validMessage = $msg; }
}
