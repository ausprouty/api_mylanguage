<?php

namespace App\Models\BibleStudy;

use ReflectionClass;

abstract class BaseStudyReferenceModel
{
    protected int $lesson;
    protected string $description;
    protected string $description_twig_key;
    protected string $reference;
    protected string $testament;
    protected string $passage_reference_info;
    protected ?string $bookName = null;
    protected ?string $bookID = null;
    protected ?string $chapterStart = null;
    protected ?string $chapterEnd = null;
    protected ?string $verseStart = null;
    protected ?string $verseEnd = null;
    protected ?string $passageID = null;
    protected ?string $uversionBookID = null;

    /**
     * Constructor accepts an associative array and dynamically assigns properties.
     *
     * @param array $data Associative array with keys matching property names.
     */
    public function __construct(array $data)
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

    /**
     * Generic Getter
     *
     * @param string $name Property name to retrieve.
     * @return mixed The value of the property.
     * @throws \Exception If the property does not exist.
     */
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \Exception("Property '{$name}' does not exist.");
    }

    /**
     * Generic Setter
     *
     * @param string $name Property name to set.
     * @param mixed $value Value to assign to the property.
     * @throws \Exception If the property does not exist.
     */
    public function __set(string $name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            throw new \Exception("Property '{$name}' does not exist.");
        }
    }

    /**
     * Returns a formatted reference with testament information.
     *
     * @return string The formatted reference string.
     */
    public function getReferenceInfo(): string
    {
        return "{$this->reference} ({$this->testament})";
    }

    /**
     * Returns all properties as an associative array.
     *
     * @return array Associative array of all property names and values.
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
