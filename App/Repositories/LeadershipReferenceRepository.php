<?php
namespace App\Repositories;

use App\Models\BibleStudy\LeadershipReferenceModel;
use App\Services\Database\DatabaseService;

class LeadershipReferenceRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getReferenceByLesson($lesson): ?LeadershipReferenceModel
    {
        $query = 'SELECT * FROM leadership_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];
        
        $data = $this->databaseService->fetchRow($query, $params);
        
        return $data 
            ? new LeadershipReferenceModel(
                  $data['lesson'], 
                  $data['reference'], 
                  $data['description']
              ) 
            : null;
    }
}
