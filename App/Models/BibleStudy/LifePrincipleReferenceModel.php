<?php
namespace App\Models\BibleStudy;

use ReflectionClass;

class LifePrincipleReferenceModel
{
    protected int $lesson;
    protected string $description;
    protected string $description_twig_key;
    protected string $reference;
    protected string $testament;
    protected string $passage_reference_info;
    protected string $question;
    protected string $question_twig_key;
    protected ?string $videoCode = null;
    protected int $videoSegment;
    protected ?string $startTime = null;
    protected ?string $endTime = null;

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

    public function getDescripttionTwigKey(): string
    {
        return $this->descripttion_twig_key;
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

    public function getQuestion(): string
    {
        return $this->question;
    }

    public function getQuestionTwigKey(): string
    {
        return $this->question_twig_key;
    }

    public function getVideoCode(): ?string
    {
        return $this->videoCode;
    }

    public function getVideoSegment(): int
    {
        return $this->videoSegment;
    }

    public function getStartTime(): ?string
    {
        return $this->startTime;
    }

    public function getEndTime(): ?string
    {
        return $this->endTime;
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

    public function setDescriptionTwigKey(string $descripttion_twig_key): void
    {
        $this->descripttion_twig_key = $descripttion_twig_key;
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

    public function setQuestion(string $question): void
    {
        $this->question = $question;
    }

    public function setQuestionTwigKey(string $question_twig_key): void
    {
        $this->question_twig_key = $question_twig_key;
    }

    public function setVideoCode(?string $videoCode): void
    {
        $this->videoCode = $videoCode;
    }

    public function setVideoSegment(int $videoSegment): void
    {
        $this->videoSegment = $videoSegment;
    }

    public function setStartTime(?string $startTime): void
    {
        $this->startTime = $startTime;
    }

    public function setEndTime(?string $endTime): void
    {
        $this->endTime = $endTime;
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
