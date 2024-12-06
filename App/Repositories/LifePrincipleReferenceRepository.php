<?php
namespace App\Repositories;

use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Services\Database\DatabaseService;

class LifePrincipleReferenceRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getReferenceByLesson($lesson): ?LifePrincipleReferenceModel
    {
        $query = "SELECT * FROM life_principle_references WHERE lesson = :lesson";
        $params = [':lesson' => $lesson];

        $data = $this->databaseService->fetchRow($query, $params);

        if ($data) {
            $reference = new LifePrincipleReferenceModel(
                $data['lesson'],
                $data['description'],
                $data['description_twig_key'],
                $data['reference'],
                $data['testament'],
                $data['passage_reference_info'],
                $data['question'],
                $data['question_twig_key'],
                $data['videoCode'] ?? null,
                $data['videoSegment'] ?? 0,
                $data['startTime'] ?? null,
                $data['endTime'] ?? null,
            );

            return $reference;
        }

        return null;
    }
}
