<?php

namespace App\Factories;

use App\Models\BibleStudy\DbsReferenceModel;
use App\Models\BibleStudy\LeadershipReferenceModel;
use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Services\Database\DatabaseService;

/**
 * Factory for creating and populating Three BibleReferenceModel instances.
 */
class BibleStudyReferenceFactory
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Creates a DbsReferenceModel and populates it with provided data.
     */
    public function createDbsReferenceModel($lesson): DbsReferenceModel
   
    {
        $model = new DbsReferenceModel();
        $query = 'SELECT * FROM dbs_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];

        $result = $this->databaseService->fetchRow($query, $params);

        if (!$result) {
            throw new \Exception("No record found for lesson: $lesson");
        }

        // Populate the model
        $model->setLesson($result['lesson']);
        $model->setDescription($result['description']);
        $model->setDescriptionTwigKey($result['description_twig_key']);
        $model->setReference($result['reference']);
        $model->setTestament($result['testament']);
        $model->setPassageReferenceInfo($result['passage_reference_info']);

        return $model;
    }

    /**
     * Creates a LifePrincipleReferenceModel and populates it with data from the database.
     */
    public function createLifePrincipleReferenceModel(int $lesson): LifePrincipleReferenceModel
    {
        $model = new LifePrincipleReferenceModel();
        $query = 'SELECT * FROM life_principle_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];

        $result = $this->databaseService->fetchRow($query, $params);

        if (!$result) {
            throw new \Exception("No record found for lesson: $lesson");
        }

        // Populate the model
        $model->setLesson($result['lesson']);
        $model->setDescription($result['description']);
        $model->setDescriptionTwigKey($result['descripttion_twig_key']);
        $model->setReference($result['reference']);
        $model->setTestament($result['testament']);
        $model->setPassageReferenceInfo($result['passage_reference_info']);
        $model->setQuestion($result['question']);
        $model->setQuestionTwigKey($result['question_twig_key']);
        $model->setVideoCode($result['videoCode']);
        $model->setVideoSegment($result['videoSegment']);
        $model->setStartTime($result['startTime']);
        $model->setEndTime($result['endTime']);

        return $model;
    }

    /**
     * Creates a LeadershipReferenceModel and populates it with data from the database.
     */
    public function createLeadershipReferenceModel(int $lesson): LeadershipReferenceModel
    {
        $model = new LeadershipReferenceModel();
        $query = 'SELECT * FROM leadership_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];

        $result = $this->databaseService->fetchRow($query, $params);

        if (!$result) {
            throw new \Exception("No record found for lesson: $lesson");
        }

        // Populate the model
        $model->setLesson($result['lesson']);
        $model->setDescription($result['description']);
        $model->setDescriptionTwigKey($result['description_twig_key']);
        $model->setReference($result['reference']);
        $model->setTestament($result['testament']);
        $model->setPassageReferenceInfo($result['passage_reference_info']);
        $model->setVideoCode($result['video_code']);
        $model->setVideoSegment($result['video_segment']);
        $model->setStartTime($result['start_time']);
        $model->setEndTime($result['end_time']);

        return $model;
    }
}

