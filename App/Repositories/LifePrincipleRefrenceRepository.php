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
            $reference = new LifePrincipleReferenceModel();
            $reference->setLesson($data['lesson']);
            $reference->setDescription($data['description']);
            $reference->setReference($data['reference']);
            $reference->setTestament($data['testament']);
            $reference->setQuestion($data['question']);
            $reference->setVideoCode($data['videoCode']);
            $reference->setVideoSegment($data['videoSegment']);
            $reference->setStartTime($data['startTime']);
            $reference->setEndTime($data['endTime']);

            return $reference;
        }

        return null;
    }
}
