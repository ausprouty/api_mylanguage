<?php

namespace App\Models\BibleStudy;

use ReflectionClass;

class LifePrincipleReferenceModel extends BaseStudyReferenceModel
{
    protected string $question;
    protected string $question_twig_key;
    protected ?string $videoCode = null;
    protected int $videoSegment;
    protected ?string $startTime = null;
    protected ?string $endTime = null;

    public function __construct(
        int $lesson,
        string $description,
        string $description_twig_key,
        string $reference,
        string $testament,
        string $passage_reference_info,
        string $question,
        string $question_twig_key,
        ?string $videoCode = null,
        int $videoSegment = 0,
        ?string $startTime = null,
        ?string $endTime = null
    ) {
        parent::__construct(
            $lesson,
            $description,
            $description_twig_key,
            $reference,
            $testament,
            $passage_reference_info
        );
        $this->question = $question;
        $this->question_twig_key = $question_twig_key;
        $this->videoCode = $videoCode;
        $this->videoSegment = $videoSegment;
        $this->startTime = $startTime;
        $this->endTime = $endTime;
    }

    // Getters
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
     * Returns all properties as an associative array, including inherited ones.
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
