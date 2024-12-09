<?php

namespace App\Repositories;

use App\Models\BibleStudy\DbsReferenceModel;
use App\Services\Database\DatabaseService;

class DbsReferenceRepository extends BaseStudyRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Retrieves a reference by lesson and returns a DbsReferenceModel instance.
     *
     * @param string $lesson
     * @return DbsReferenceModel|null
     */
    public function getReferenceByLesson(string $lesson): ?DbsReferenceModel
    {
        $query = 'SELECT * FROM study_study_dbs_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];

        $data = $this->databaseService->fetchRow($query, $params);

        if ($data) {

            $reference = [
                $data['lesson'],
                $data['description'],
                $data['description_twig_key'],
                $data['reference'],
                $data['testament'],
                $data['passage_reference'],

            ];
            $model = new DbsReferenceModel($data);
            return $model;
        }
    }
}
