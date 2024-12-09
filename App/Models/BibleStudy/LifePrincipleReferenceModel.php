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
     * Constructor accepts an associative array to populate properties.
     *
     * @param array $data Associative array with keys matching property names.
     */
    public function __construct(array $data)
    {
        // Call the parent constructor to handle BaseStudyReferenceModel properties
        parent::__construct($data);

        // Assign additional properties specific to LifePrincipleReferenceModel
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
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
