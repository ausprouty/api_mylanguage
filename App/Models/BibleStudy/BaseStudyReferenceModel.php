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

    public function __construct(
        int $lesson,
        string $description,
        string $description_twig_key,
        string $reference,
        string $testament,
        string $passage_reference_info
    ) {
        $this->lesson = $lesson;
        $this->description = $description;
        $this->description_twig_key = $description_twig_key;
        $this->reference = $reference;
        $this->testament = $testament;
        $this->passage_reference_info = $passage_reference_info;
    }

    // Generic Getter
    public function __get(string $name)
    {
        if (property_exists($this, $name)) {
            return $this->$name;
        }
        throw new \Exception("Property '{$name}' does not exist.");
    }

    // Generic Setter
    public function __set(string $name, $value): void
    {
        if (property_exists($this, $name)) {
            $this->$name = $value;
        } else {
            throw new \Exception("Property '{$name}' does not exist.");
        }
    }

    public function getReferenceInfo(): string
    {
        return "{$this->reference} ({$this->testament})";
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
