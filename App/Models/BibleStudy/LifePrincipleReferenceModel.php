<?php

namespace App\Models\BibleStudy;

class LifePrincipleReferenceModel extends BaseStudyReferenceModel
{
    protected string $question;
    protected string $question_twig_key;
    protected ?string $videoCode = null;
    protected int $videoSegment = 0;
    protected ?string $startTime = null;
    protected ?string $endTime = null;


    /**
     * Constructor to initialize default values for the derived class.
     */
    public function __construct()
    {
        // Call parent constructor to initialize base properties
        parent::__construct();

        // Initialize new properties for LifePrincipleReferenceModel
        $this->question = '';            // Default empty string
        $this->question_twig_key = '';   // Default empty string
        $this->videoCode = null;         // Default null
        $this->videoSegment = 0;         // Default integer value
        $this->startTime = null;         // Default null
        $this->endTime = null;           // Default null
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
}
