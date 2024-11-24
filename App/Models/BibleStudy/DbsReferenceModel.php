<?php
namespace App\Models\BibleStudy;
use ReflectionClass;

class DbsReferenceModel {
    protected $lesson;
    protected $reference;
    protected $testament;
    protected $passage_reference_info;
    protected $description;
    protected $twig_key;

    public function __construct() {
    }

    // Getters
    public function getDescription()
    {
        return $this->description;
    }

    public function getLesson()
    {
        return $this->lesson;
    }

    public function getPassageReferenceInfo()
    {
        return $this->passage_reference_info;
    }

    public function getReference()
    {
        return $this->reference;
    }

    public function getTestament()
    {
        return $this->testament;
    }

    public function getTwigKey()
    {
        return $this->twig_key;
    }

    // Setters
    public function setDescription($description)
    {
        $this->description = $description;
    }

    public function setLesson($lesson)
    {
        $this->lesson = $lesson;
    }

    public function setPassageReferenceInfo($passage_reference_info)
    {
        $this->passage_reference_info = $passage_reference_info;
    }

    public function setReference($reference)
    {
        $this->reference = $reference;
    }

    public function setTestament($testament)
    {
        $this->testament = $testament;
    }

    public function setTwigKey($twig_key)
    {
        $this->twig_key = $twig_key;
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
