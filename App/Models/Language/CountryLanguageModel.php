<?php
declare(strict_types=1);

namespace App\Models\Language;

use JsonSerializable;

class CountryLanguageModel implements JsonSerializable
{
    private ?int $id = null;
    private ?string $countryCode = null;
    private ?string $languageCodeIso = null;
    private ?string $languageCodeHL = null;
    private ?string $languageNameEnglish = null;
    private ?string $languageCodeJF = null; // optional, computed

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
            'id'                  => $this->id,
            'countryCode'         => $this->countryCode,
            'languageCodeIso'     => $this->languageCodeIso,
            'languageCodeHL'      => $this->languageCodeHL,
            'languageNameEnglish' => $this->languageNameEnglish,
            'languageCodeJF'      => $this->languageCodeJF,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // --- Getters ---
    public function getId(): ?int { return $this->id; }
    public function getCountryCode(): ?string { return $this->countryCode; }
    public function getLanguageCodeIso(): ?string { return $this->languageCodeIso; }
    public function getLanguageCodeHL(): ?string { return $this->languageCodeHL; }
    public function getLanguageNameEnglish(): ?string
    { return $this->languageNameEnglish; }
    public function getLanguageCodeJF(): ?string { return $this->languageCodeJF; }

    // --- Setters ---
    public function setId(?int $id): void { $this->id = $id; }
    public function setCountryCode(?string $countryCode): void
    { $this->countryCode = $countryCode; }
    public function setLanguageCodeIso(?string $languageCodeIso): void
    { $this->languageCodeIso = $languageCodeIso; }
    public function setLanguageCodeHL(?string $languageCodeHL): void
    { $this->languageCodeHL = $languageCodeHL; }
    public function setLanguageNameEnglish(?string $name): void
    { $this->languageNameEnglish = $name; }
    public function setLanguageCodeJF(?string $code): void
    { $this->languageCodeJF = $code; }

    /**
     * Compute and set languageCodeJF using a resolver:
     *   fn(string $hl): ?string
     */
    public function withLanguageCodeJF(callable $resolver): self
    {
        $hl = $this->languageCodeHL ?? '';
        $this->languageCodeJF = $hl !== '' ? ($resolver)($hl) : null;
        return $this;
    }

    /**
     * Batch helper: augment an array of models with JF codes.
     *
     * @param CountryLanguageModel[] $languages
     * @param callable(string):(?string) $resolver
     * @return CountryLanguageModel[]
     */
    public static function addLanguageCodeJF(
        array $languages,
        callable $resolver
    ): array {
        foreach ($languages as $lang) {
            if ($lang instanceof self) {
                $lang->withLanguageCodeJF($resolver);
            }
        }
        return $languages;
    }
}
