<?php

namespace App\Repositories;

use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Services\Database\DatabaseService;

class LifePrincipleReferenceRepository extends BaseStudyRepository
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    public function getReferenceByLesson($lesson): ?LifePrincipleReferenceModel
    {
        $query = "SELECT * FROM study_principle_references WHERE lesson = :lesson";
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
            if ($data['passage_reference_info']) {
                $json = json_decode($data['passage_reference_info']);
                if (is_array($json)) { // Check if decoding was successful
                    $reference['chapterStart'] = $json['chapterStart'] ?? null; // Assign values to $reference array
                    $reference['chapterEnd'] = $json['chapterEnd'] ?? null;
                    $reference['verseStart'] = $json['verseStart'] ?? null;
                    $reference['verseEnd'] = $json['verseEnd'] ?? null;
                    $reference['passageID'] = $json['passageID'] ?? null;
                    $reference['uversionBookID'] = $json['uversionBookID'] ?? null;
                } else {
                    // Handle the case where $json is null (invalid JSON or decoding failed)
                    $reference['chapterStart'] = null;
                    $reference['chapterEnd'] = null;
                    $reference['verseStart'] = null;
                    $reference['verseEnd'] = null;
                    $reference['passageID'] = null;
                    $reference['uversionBookID'] = null;

                    // Optionally log an error or throw an exception if $data['passage_reference_info'] is invalid
                    error_log('Failed to decode passage_reference_info: ' . $data['passage_reference_info']);
                }
                return $reference;
            }
        }

        return null;
    }
}
