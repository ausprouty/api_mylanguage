<?php
declare(strict_types=1);

namespace App\Models\Bible;

use JsonSerializable;

/**
 * Represents a Bible resource, including metadata for language, media types,
 * and fileset classification. Used for integration with sources like
 * BibleBrain.
 */
class BibleModel implements JsonSerializable
{
    // --- Core metadata ---
    private ?string $abbreviation = null;
    private int $audio = 0;              // 0/1 (persisted as int)
    private ?int $bid = null;
    private ?string $collectionCode = null;
    private ?string $dateVerified = null; // YYYY-MM-DD
    private ?string $direction = null;    // "ltr" | "rtl"
    private ?string $externalId = null;   // e.g., BibleBrain fileset id
    private ?string $format = null;       // e.g., text_plain, text_usx
    private ?string $idBibleGateway = null;

    // --- Language codes / labels ---
    private ?int $languageCodeBibleBrain = null; // keep as int if that's your id
    private ?string $languageCodeDrupal = null;
    private ?string $languageCodeHL = null;
    private ?string $languageCodeIso = null;
    private ?string $languageEnglish = null;
    private ?string $languageName = null;

    // --- Rendering / typography hints ---
    private ?string $noBoldPdf = null;
    private ?string $numerals = null;
    private ?string $spacePdf = null;

    // --- Media flags (persisted as ints 0/1) ---
    private int $text = 0;
    private int $video = 0;

    // --- Volume metadata ---
    private ?string $volumeName = null;
    private ?string $volumeNameAlt = null;
    private ?int $weight = null;

    /**
     * Hydrate from an associative array (keys must match properties).
     */
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
     * Reset all media flags to 0 (false).
     */
    public function resetMediaFlags(): void
    {
        $this->text = 0;
        $this->audio = 0;
        $this->video = 0;
    }

    /**
     * Preferred JSON/array projection for logs and APIs.
     */
    public function toArray(): array
    {
        return [
            'abbreviation'            => $this->abbreviation,
            'audio'                   => $this->audio,
            'bid'                     => $this->bid,
            'collectionCode'          => $this->collectionCode,
            'dateVerified'            => $this->dateVerified,
            'direction'               => $this->direction,
            'externalId'              => $this->externalId,
            'format'                  => $this->format,
            'idBibleGateway'          => $this->idBibleGateway,
            'languageCodeBibleBrain'  => $this->languageCodeBibleBrain,
            'languageCodeDrupal'      => $this->languageCodeDrupal,
            'languageCodeHL'          => $this->languageCodeHL,
            'languageCodeIso'         => $this->languageCodeIso,
            'languageEnglish'         => $this->languageEnglish,
            'languageName'            => $this->languageName,
            'noBoldPdf'               => $this->noBoldPdf,
            'numerals'                => $this->numerals,
            'source'                  => $this->source,
            'spacePdf'                => $this->spacePdf,
            'text'                    => $this->text,
            'video'                   => $this->video,
            'volumeName'              => $this->volumeName,
            'volumeNameAlt'           => $this->volumeNameAlt,
            'weight'                  => $this->weight,
        ];
    }

    public function jsonSerialize(): array
    {
        return $this->toArray();
    }

    // ----------------------
    // Getters
    // ----------------------

    public function getAbbreviation(): ?string { return $this->abbreviation; }
    public function getAudio(): int { return $this->audio; }
    public function getBid(): ?int { return $this->bid; }
    public function getCollectionCode(): ?string { return $this->collectionCode; }
    public function getDateVerified(): ?string { return $this->dateVerified; }
    public function getDirection(): ?string { return $this->direction; }
    public function getExternalId(): ?string { return $this->externalId; }
    public function getFormat(): ?string { return $this->format; }
    public function getIdBibleGateway(): ?string { return $this->idBibleGateway; }
    public function getLanguageCodeBibleBrain(): ?int
    { return $this->languageCodeBibleBrain; }
    public function getLanguageCodeDrupal(): ?string
    { return $this->languageCodeDrupal; }
    public function getLanguageCodeHL(): ?string { return $this->languageCodeHL; }
    public function getLanguageCodeIso(): ?string { return $this->languageCodeIso; }
    public function getLanguageEnglish(): ?string
    { return $this->languageEnglish; }
    public function getLanguageName(): ?string { return $this->languageName; }
    public function getNoBoldPdf(): ?string { return $this->noBoldPdf; }
    public function getNumerals(): ?string { return $this->numerals; }

    // Note: 'source' was in original getters; keep it and add backing field.
    private ?string $source = null;
    public function getSource(): ?string { return $this->source; }

    public function getSpacePdf(): ?string { return $this->spacePdf; }
    public function getText(): int { return $this->text; }
    public function getVideo(): int { return $this->video; }
    public function getVolumeName(): ?string { return $this->volumeName; }
    public function getVolumeNameAlt(): ?string { return $this->volumeNameAlt; }
    public function getWeight(): ?int { return $this->weight; }

    // ----------------------
    // Setters
    // ----------------------

    public function setAbbreviation(?string $v): void { $this->abbreviation = $v; }
    public function setAudio(bool $v): void { $this->audio = $v ? 1 : 0; }
    public function setBid(?int $v): void { $this->bid = $v; }
    public function setCollectionCode(?string $v): void { $this->collectionCode = $v; }
    public function setDateVerified(?string $v): void
    {
        if ($v !== null &&
            !\preg_match('/^\d{4}-\d{2}-\d{2}$/', $v)) {
            throw new \InvalidArgumentException(
                'dateVerified must be YYYY-MM-DD or null'
            );
        }
        $this->dateVerified = $v;
    }
    public function setDirection(?string $v): void { $this->direction = $v; }
    public function setExternalId(?string $v): void { $this->externalId = $v; }
    public function setFormat(?string $v): void { $this->format = $v; }
    public function setIdBibleGateway(?string $v): void
    { $this->idBibleGateway = $v; }

    public function setLanguageCodeBibleBrain(?int $v): void
    { $this->languageCodeBibleBrain = $v; }

    public function setLanguageCodeDrupal(?string $v): void
    { $this->languageCodeDrupal = $v; }

    public function setLanguageCodeHL(?string $v): void
    { $this->languageCodeHL = $v; }

    public function setLanguageCodeIso(?string $v): void
    { $this->languageCodeIso = $v; }

    public function setLanguageEnglish(?string $v): void
    { $this->languageEnglish = $v; }

    public function setLanguageName(?string $v): void
    { $this->languageName = $v; }

    public function setNoBoldPdf(?string $v): void { $this->noBoldPdf = $v; }
    public function setNumerals(?string $v): void { $this->numerals = $v; }
    public function setSource(?string $v): void { $this->source = $v; }
    public function setSpacePdf(?string $v): void { $this->spacePdf = $v; }
    public function setText(bool $v): void { $this->text = $v ? 1 : 0; }
    public function setVideo(bool $v): void { $this->video = $v ? 1 : 0; }
    public function setVolumeName(?string $v): void { $this->volumeName = $v; }
    public function setVolumeNameAlt(?string $v): void
    { $this->volumeNameAlt = $v; }
    public function setWeight(?int $v): void { $this->weight = $v; }
}
