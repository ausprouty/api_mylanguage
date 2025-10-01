<?php
declare(strict_types=1);

namespace App\Models;

use JsonSerializable;

class AskQuestionModel implements JsonSerializable
{
    private ?int $id = null;
    private string $languageCodeHL = '';
    private string $name = '';
    private string $ethnicName = '';
    private string $url = '';
    private string $contactPage = '';
    private string $languageCodeTracts = '';
    private string $promoText = '';
    private string $promoImage = '';
    private string $tagline = '';
    private int $weight = 0;

    public function __construct(
        string $languageCodeHL = '',
        string $name = '',
        string $ethnicName = '',
        string $url = '',
        string $contactPage = '',
        string $languageCodeTracts = '',
        string $promoText = '',
        string $promoImage = '',
        string $tagline = '',
        int $weight = 0
    ) {
        $this->languageCodeHL   = $languageCodeHL;
        $this->name             = $name;
        $this->ethnicName       = $ethnicName;
        $this->url              = $url;
        $this->contactPage      = $contactPage;
        $this->languageCodeTracts = $languageCodeTracts;
        $this->promoText        = $promoText;
        $this->promoImage       = $promoImage;
        $this->tagline          = $tagline;
        $this->weight           = $weight;
    }

    /** Hydrate from assoc array (keys must match properties). */
    public function populate(array $data): self
    {
        foreach ($data as $k => $v) {
            if (\property_exists($this, $k)) {
                $this->$k = $v;
            }
        }
        return $this;
    }

    /**
     * Back-compat setter for DB rows (array or object).
     * Prefer using populate() with arrays going forward.
     */
    public function setValues(object|array $data): void
    {
        $arr = \is_array($data) ? $data : (array) $data;
        $this->populate($arr);
    }

    /** Canonical array representation. */
    public function toArray(): array
    {
        return [
            'id'                 => $this->id,
            'languageCodeHL'     => $this->languageCodeHL,
            'name'               => $this->name,
            'ethnicName'         => $this->ethnicName,
            'url'                => $this->url,
            'contactPage'        => $this->contactPage,
            'languageCodeTracts' => $this->languageCodeTracts,
            'promoText'          => $this->promoText,
            'promoImage'         => $this->promoImage,
            'tagline'            => $this->tagline,
            'weight'             => $this->weight,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // Getters
    public function getId(): ?int { return $this->id; }
    public function getLanguageCodeHL(): string { return $this->languageCodeHL; }
    public function getName(): string { return $this->name; }
    public function getEthnicName(): string { return $this->ethnicName; }
    public function getUrl(): string { return $this->url; }
    public function getContactPage(): string { return $this->contactPage; }
    public function getLanguageCodeTracts(): string
    { return $this->languageCodeTracts; }
    public function getPromoText(): string { return $this->promoText; }
    public function getPromoImage(): string { return $this->promoImage; }
    public function getTagline(): string { return $this->tagline; }
    public function getWeight(): int { return $this->weight; }

    // Setters
    public function setId(?int $id): void { $this->id = $id; }
    public function setLanguageCodeHL(string $v): void { $this->languageCodeHL = $v; }
    public function setName(string $v): void { $this->name = $v; }
    public function setEthnicName(string $v): void { $this->ethnicName = $v; }
    public function setUrl(string $v): void { $this->url = $v; }
    public function setContactPage(string $v): void { $this->contactPage = $v; }
    public function setLanguageCodeTracts(string $v): void
    { $this->languageCodeTracts = $v; }
    public function setPromoText(string $v): void { $this->promoText = $v; }
    public function setPromoImage(string $v): void { $this->promoImage = $v; }
    public function setTagline(string $v): void { $this->tagline = $v; }
    public function setWeight(int $v): void { $this->weight = $v; }
}
