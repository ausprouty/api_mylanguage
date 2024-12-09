<?php

namespace App\Repositories;

use App\Models\BibleStudy\LeadershipReferenceModel;
use App\Services\Database\DatabaseService;

class LeadershipReferenceRepository extends BaseStudyRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getReferenceByLesson($lesson): ?LeadershipReferenceModel
    {
        $query = 'SELECT * FROM study_leadership_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];

        $data = $this->databaseService->fetchRow($query, $params);

        if ($data){
            new LeadershipReferenceModel(
                $data['lesson'],
                $data['description'],
                $data['description_twig_key'],
                $data['reference'],
                $data['testament'],
                $data['passage_reference_info'],
                $data['video_code'],
                $data['video_segment'],
                $data['start_time'],
                $data['end_time'],
            );
            if ($data['passage_reference_info']) {
                $json = json_decode($data['passage_reference_info']);
                if (is_array($json)) { // Check if decoding was successful
                    $data['chapterStart'] = $json['chapterStart'] ?? null; // Assign values to $data array
                    $data['chapterEnd'] = $json['chapterEnd'] ?? null;
                    $data['verseStart'] = $json['verseStart'] ?? null;
                    $data['verseEnd'] = $json['verseEnd'] ?? null;
                    $data['passageID'] = $json['passageID'] ?? null;
                    $data['uversionBookID'] = $json['uversionBookID'] ?? null;
                } else {
                    // Handle the case where $json is null (invalid JSON or decoding failed)
                    $data['chapterStart'] = null;
                    $data['chapterEnd'] = null;
                    $data['verseStart'] = null;
                    $data['verseEnd'] = null;
                    $data['passageID'] = null;
                    $data['uversionBookID'] = null;

                    // Optionally log an error or throw an exception if $data['passage_reference_info'] is invalid
                    error_log('Failed to decode passage_reference_info: ' . $data['passage_reference_info']);
                }
                return $reference;
            }
        }
         else{
        return null;
         }
    
    }
}
