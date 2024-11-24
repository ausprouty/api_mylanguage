<?php

namespace App\Factories;

use App\Models\BibleStudy\DbsReferenceModel;
use App\Services\Database\DatabaseService;

/**
 * Factory for creating and populating DbsReferenceModel instances.
 */
class DbsReferenceModelFactory
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Creates a DbsReferenceModel and populates it with provided data.
     */
    public function create($lesson): DbsReferenceModel
    {
        $model = new DbsReferenceModel();
        $query = 'SELECT * FROM dbs_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];

        // Fetch the result from the database
        $result = $this->databaseService->fetch($query, $params);

        // If no record is found, handle accordingly (throw an exception, return null, etc.)
        if (!$result) {
            throw new \Exception("No reference found for lesson: $lesson");
        }

        // Populate the model
        $model->setLesson($result['lesson'] ?? null);
        $model->setReference($result['reference'] ?? null);
        $model->setTestament($result['testament'] ?? null);
        $model->setPassageReferenceInfo($result['passage_reference_info'] ?? null);
        $model->setDescription($result['description'] ?? null);
        $model->setTwigKey($result['twig_key'] ?? null);

        return $model;
    }
}
