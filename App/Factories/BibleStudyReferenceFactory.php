<?php

namespace App\Factories;

use App\Models\BibleStudy\DbsReferenceModel;
use App\Models\BibleStudy\LeadershipReferenceModel;
use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Services\Database\DatabaseService;
use App\Repositories\PassageReferenceRepository;
use Exception;

/**
 * Factory for creating and populating Bible Study Reference Models.
 */
class BibleStudyReferenceFactory
{
    private DatabaseService $databaseService;
    private PassageReferenceRepository $passageReferenceRepository;

    /**
     * Constructor to inject the DatabaseService.
     *
     * @param DatabaseService $databaseService The database service instance.
     */
    public function __construct(
        DatabaseService $databaseService, 
        PassageReferenceRepository $passageReferenceRepository
    )
    {
        $this->databaseService = $databaseService;
        $this->passageReferenceRepository = $passageReferenceRepository;
    }

    /**
     * Creates a study reference model based on the study type.
     *
     * @param string $study The type of study (e.g., 'dbs', 'principle', 'leader').
     * @param int $lesson The lesson identifier.
     * @return mixed The created reference model.
     * @throws Exception If the study type is invalid or data is not found.
     */
    public function createModel(string $study, int $lesson)
    {
        switch ($study) {
            case 'dbs':
                return $this->createDbsReferenceModel($lesson);
            case 'principle':
                return $this->createLifePrincipleReferenceModel($lesson);
            case 'leader':
                return $this->createLeadershipReferenceModel($lesson);
            default:
                throw new Exception("Invalid study type: $study");
        }
    }

    /**
     * Creates and populates a DbsReferenceModel.
     *
     * @param int $lesson The lesson identifier.
     * @return DbsReferenceModel The populated model.
     * @throws Exception If no data is found for the given lesson.
     */
    public function createDbsReferenceModel(int $lesson): DbsReferenceModel
    {
        $query = 'SELECT * FROM study_dbs_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];
        $data = $this->databaseService->fetchRow($query, $params);

        if (!$data) {
            throw new Exception("No record found for lesson: $lesson");
        }

        $result = $this->expandPassageReferenceInfo($data);
        $missing = $this->validatePassageData ($result);
        if ($missing){
            $result = $this->populateMissingValues($result);
        }
        return (new DbsReferenceModel())->populate($result);
    }

    /**
     * Creates and populates a LifePrincipleReferenceModel.
     *
     * @param int $lesson The lesson identifier.
     * @return LifePrincipleReferenceModel The populated model.
     * @throws Exception If no data is found for the given lesson.
     */
    public function createLifePrincipleReferenceModel(
        int $lesson
    ): LifePrincipleReferenceModel {
        $query = 'SELECT * FROM study_life_principle_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];
        $data = $this->databaseService->fetchRow($query, $params);

        if (!$data) {
            throw new Exception("No record found for lesson: $lesson");
        }

        $result = $this->expandPassageReferenceInfo($data);
        return (new LifePrincipleReferenceModel())->populate($result);
    }

    /**
     * Creates and populates a LeadershipReferenceModel.
     *
     * @param int $lesson The lesson identifier.
     * @return LeadershipReferenceModel The populated model.
     * @throws Exception If no data is found for the given lesson.
     */
    public function createLeadershipReferenceModel(
        int $lesson
    ): LeadershipReferenceModel {
        $query = 'SELECT * FROM study_leadership_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];
        $data = $this->databaseService->fetchRow($query, $params);

        if (!$data) {
            throw new Exception("No record found for lesson: $lesson");
        }

        $result = $this->expandPassageReferenceInfo($data);
        return (new LeadershipReferenceModel())->populate($result);
    }

    /**
     * Expands the passage_reference_info field into detailed fields.
     *
     * @param array $reference The reference data.
     * @return array The expanded reference data.
     */
    protected function expandPassageReferenceInfo(array $reference): array
    {
        $json = json_decode($reference['passage_reference_info'] ?? '', true);

        if (is_array($json)) {
            $reference['bookID'] = $json['bookID'] ?? null;
            $reference['bookName'] = $json['bookName'] ?? null;
            $reference['bookNumber'] = $json['bookNumber'] ?? 0;
            $reference['chapterStart'] = $json['chapterStart'] ?? null;
            $reference['chapterEnd'] = $json['chapterEnd'] ?? null;
            $reference['verseStart'] = $json['verseStart'] ?? null;
            $reference['verseEnd'] = $json['verseEnd'] ?? null;
            $reference['passageID'] = $json['passageID'] ?? null;
            $reference['uversionBookID'] = $json['uversionBookID'] ?? null;
        } else {
            $reference['bookID'] = null;
            $reference['bookName'] = null;
            $reference['bookNumber'] =  0;
            $reference['chapterStart'] = null;
            $reference['chapterEnd'] = null;
            $reference['verseStart'] = null;
            $reference['verseEnd'] = null;
            $reference['passageID'] = null;
            $reference['uversionBookID'] = null;

            error_log(
                'Failed to decode passage_reference_info: ' .
                    ($reference['passage_reference_info'] ?? '')
            );
        }

        return $reference;
    }

    function validatePassageData($data) {
        $requiredFields = [
            'entry', 'bookName', 'bookID', 'uversionBookID', 
            'bookNumber', 'testament', 'chapterStart', 'verseStart', 
             'verseEnd', 'passageID'
        ];
        
        $missingFields = [];
        
        foreach ($requiredFields as $field) {
            if (empty($data[$field])) {
                $missingFields[] = $field;
            }
        }
        
        return $missingFields;
    }

    function populateMissingValues($data) {
    
        if ($data['bookID'] == null) {
            $data['bookID'] = $this->passageReferenceRepository->findBookID($data['bookName']);
        }
        if ($data['bookNumber'] == 0) {
            $data['bookNumber'] =  $this->passageReferenceRepository->findBookNumber($data['bookID']);
        }
        if ($data['uversionBookID'] == 0) {
            $data['uversionBookID'] =  $this->passageReferenceRepository->findUversionBookID($data['bookID']);
        }
        
        return $data;
    }
    function saveCorrectedData($data, $dbConnection) {
    }
}
