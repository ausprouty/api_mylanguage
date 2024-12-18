<?php

namespace App\Models\Bible;

use ReflectionClass;

/**
 * Represents a Bible Passage model with related data and methods.
 */
class PassageModel
{
    /**
     * @var string The Bible passage ID.
     */
    public $bpid;

    /**
     * @var string|null The date when the passage was last checked.
     */
    private $dateChecked;

    /**
     * @var string|null The date when the passage was last used.
     */
    private $dateLastUsed;

    /**
     * @var string The text of the Bible passage.
     */
    private $passageText;

    /**
     * @var string The URL of the Bible passage.
     */
    private $passageUrl;

    /**
     * @var string The reference of the passage in the local language.
     */
    private $referenceLocalLanguage;

    /**
     * @var int The number of times the passage has been used.
     */
    private $timesUsed;

    /**
     * Initializes a new instance of the PassageModel class.
     */
    public function __construct()
    {
        $this->bpid = '';
        $this->dateChecked = null;
        $this->dateLastUsed = null;
        $this->passageText = '';
        $this->passageUrl = '';
        $this->referenceLocalLanguage = '';
        $this->timesUsed = 0;
    }

    /**
     * Gets the Bible passage ID.
     *
     * @return string The Bible passage ID.
     */
    public function getBpid(): string
    {
        return $this->bpid;
    }

    /**
     * Gets the date when the passage was last checked.
     *
     * @return string|null The date last checked.
     */
    public function getDateChecked(): ?string
    {
        return $this->dateChecked;
    }

    /**
     * Gets the date when the passage was last used.
     *
     * @return string|null The date last used.
     */
    public function getDateLastUsed(): ?string
    {
        return $this->dateLastUsed;
    }

    /**
     * Gets the text of the Bible passage.
     *
     * @return string The passage text.
     */
    public function getPassageText(): string
    {
        return $this->passageText;
    }

    /**
     * Gets the URL of the Bible passage.
     *
     * @return string The passage URL.
     */
    public function getPassageUrl(): string
    {
        return $this->passageUrl;
    }

    /**
     * Returns the properties as an associative array.
     *
     * @return array
     */
    public function getProperties(): array
    {
        $reflection = new ReflectionClass($this);
        $properties = $reflection->getProperties();
        $propsArray = [];

        foreach ($properties as $property) {
            $property->setAccessible(true); // Allows access to private property
            $propsArray[$property->getName()] = $property->getValue($this);
        }

        return $propsArray;
    }

    /**
     * Gets the reference of the passage in the local language.
     *
     * @return string The local language reference.
     */
    public function getReferenceLocalLanguage(): string
    {
        return $this->referenceLocalLanguage;
    }

    /**
     * Gets the number of times the passage has been used.
     *
     * @return int The usage count.
     */
    public function getTimesUsed(): int
    {
        return $this->timesUsed;
    }

    /**
     * Sets the Bible passage ID.
     *
     * @param string $bpid The Bible passage ID to set.
     */
    public function setBpid(string $bpid): void
    {
        $this->bpid = $bpid;
    }

    /**
     * Sets the date when the passage was last checked.
     *
     * @param string|null $date The date to set.
     */
    public function setDateChecked(?string $date): void
    {
        $this->dateChecked = $date;
    }

    /**
     * Sets the date when the passage was last used.
     *
     * @param string|null $date The date to set.
     * @throws \InvalidArgumentException If the date format is invalid.
     */
    public function setDateLastUsed(?string $date): void
    {
        if ($date && !preg_match('/^\d{4}-\d{2}-\d{2}$/', $date)) {
            throw new \InvalidArgumentException('Invalid date format');
        }
        $this->dateLastUsed = $date;
    }

    /**
     * Sets the text of the Bible passage.
     *
     * @param string $passageText The passage text to set.
     */
    public function setPassageText(string $passageText): void
    {
        $this->passageText = $passageText;
    }

    /**
     * Sets the URL of the Bible passage.
     *
     * @param string $passageUrl The passage URL to set.
     */
    public function setPassageUrl(string $passageUrl): void
    {
        $this->passageUrl = $passageUrl;
    }

    /**
     * Sets the reference of the passage in the local language.
     *
     * @param string $reference The local language reference to set.
     */
    public function setReferenceLocalLanguage(string $reference): void
    {
        $this->referenceLocalLanguage = $reference;
    }

    /**
     * Sets the number of times the passage has been used.
     *
     * @param int $times The usage count to set.
     */
    public function setTimesUsed(int $times): void
    {
        $this->timesUsed = $times;
    }

    /**
     * Updates the usage statistics for the passage.
     */
    public function updateUsage(): void
    {
        $this->dateLastUsed = date("Y-m-d");
        $this->timesUsed++;
    }
}
