<?php
namespace App\Models\BibleStudy;

use ReflectionClass;

class LeadershipReferenceModel
{
    protected int $lesson;
    protected string $description;
    protected string $description_twig_key;
    protected string $reference;
    protected string $testament;
    protected string $passage_reference_info;
    protected string $video_code;
    protected int $video_segment;
    protected string $start_time;
    protected string $end_time;

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

    public function getVideoCode(): string
    {
        return $this->video_code;
    }

    public function getVideoSegment(): int
    {
        return $this->video_segment;
    }

    public function getStartTime(): string
    {
        return $this->start_time;
    }

    public function getEndTime(): string
    {
        return $this->end_time;
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

    public function setVideoCode(string $video_code): void
    {
        $this->video_code = $video_code;
    }

    public function setVideoSegment(int $video_segment): void
    {
        $this->video_segment = $video_segment;
    }

    public function setStartTime(string $start_time): void
    {
        $this->start_time = $start_time;
    }

    public function setEndTime(string $end_time): void
    {
        $this->end_time = $end_time;
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
