<?php
namespace App\Models\BibleStudy;

use ReflectionClass;

class DbsReferenceModel
{
    protected int $lesson;
    protected string $description;
    protected string $description_twig_key;
    protected string $reference;
    protected string $testament;
    protected string $passage_reference_info;

    public function __construct()
    {
    }

    // Getters
    public function getLesson(): int
    {
        return $this->lesson;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getDescriptionTwigKey(): string
    {
        return $this->description_twig_key;
    }

    public function getReference(): string
    {
        return $this->reference;
    }

    public function getTestament(): string
    {
        return $this->testament;
    }

    public function getPassageReferenceInfo(): string
    {
        return $this->passage_reference_info;
    }

    // Setters
    public function setLesson(int $lesson): void
    {
        $this->lesson = $lesson;
    }

    public function setDescription(string $description): void
    {
        $this->description = $description;
    }

    public function setDescriptionTwigKey(string $description_twig_key): void
    {
        $this->description_twig_key = $description_twig_key;
    }

    public function setReference(string $reference): void
    {
        $this->reference = $reference;
    }

    public function setTestament(string $testament): void
    {
        $this->testament = $testament;
    }

    public function setPassageReferenceInfo(string $passage_reference_info): void
    {
        $this->passage_reference_info = $passage_reference_info;
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
?>