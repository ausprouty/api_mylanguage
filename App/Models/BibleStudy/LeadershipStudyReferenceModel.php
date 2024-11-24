<?php

namespace App\Models\BibleStudy;

class LeadershipStudyReferenceModel extends BaseStudyReferenceModel
{
    protected string $video_code;
    protected int $video_segment;
    protected string $start_time;
    protected string $end_time;

    public function __construct(
        int $lesson,
        string $description,
        string $description_twig_key,
        string $reference,
        string $testament,
        string $passage_reference_info,
        string $video_code,
        int $video_segment,
        string $start_time,
        string $end_time
    ) {
        parent::__construct($lesson, $description, $description_twig_key, $reference, $testament, $passage_reference_info);
        $this->video_code = $video_code;
        $this->video_segment = $video_segment;
        $this->start_time = $start_time;
        $this->end_time = $end_time;
    }

    // You can add any additional getters or setters specific to this class if needed.
}
