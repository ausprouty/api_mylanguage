<?php
declare(strict_types=1);

namespace App\Models\Country;

use JsonSerializable;

class CountryModel implements JsonSerializable
{
    private ?string $countryCodeIso = null;   // e.g., "AU"
    private ?string $countryCodeIso3 = null;  // e.g., "AUS"
    private ?string $countryNameEnglish = null;
    private ?string $countryName = null;      // local or preferred name
    private ?string $continentCode = null;    // e.g., "OC", "EU"
    private ?string $continentName = null;    // e.g., "Oceania"
    private ?bool $inEuropeanUnion = null;

    /**
     * Populate from an associative array. Keys must match properties.
     * Backward-compat: accepts legacy 'contenentName' and maps it.
     */
    public function populate(array $data): void
    {
        // Backward-compat key mapping
        if (isset($data['contenentName']) && !isset($data['continentName'])) {
            $data['continentName'] = $data['contenentName'];
        }

        foreach ($data as $key => $value) {
            if (\property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    public function toArray(): array
    {
        return [
            'countryCodeIso'     => $this->countryCodeIso,
            'countryCodeIso3'    => $this->countryCodeIso3,
            'countryNameEnglish' => $this->countryNameEnglish,
            'countryName'        => $this->countryName,
            'continentCode'      => $this->continentCode,
            'continentName'      => $this->continentName,
            'inEuropeanUnion'    => $this->inEuropeanUnion,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // --- Getters ---
    public function getCountryCodeIso(): ?string
    { return $this->countryCodeIso; }

    public function getCountryCodeIso3(): ?string
    { return $this->countryCodeIso3; }

    public function getCountryNameEnglish(): ?string
    { return $this->countryNameEnglish; }

    public function getCountryName(): ?string
    { return $this->countryName; }

    public function getContinentCode(): ?string
    { return $this->continentCode; }

    public function getContinentName(): ?string
    { return $this->continentName; }

    public function isInEuropeanUnion(): ?bool
    { return $this->inEuropeanUnion; }

    // --- Setters ---
    public function setCountryCodeIso(?string $code): void
    { $this->countryCodeIso = $code; }

    public function setCountryCodeIso3(?string $code3): void
    { $this->countryCodeIso3 = $code3; }

    public function setCountryNameEnglish(?string $name): void
    { $this->countryNameEnglish = $name; }

    public function setCountryName(?string $name): void
    { $this->countryName = $name; }

    public function setContinentCode(?string $code): void
    { $this->continentCode = $code; }

    public function setContinentName(?string $name): void
    { $this->continentName = $name; }

    public function setInEuropeanUnion(?bool $inEu): void
    { $this->inEuropeanUnion = $inEu; }
}
