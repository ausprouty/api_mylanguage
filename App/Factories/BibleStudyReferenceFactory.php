<?php

namespace App\Factories;

use App\Models\BibleStudy\DbsReferenceModel;
use App\Models\BibleStudy\LeadershipReferenceModel;
use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Services\Database\DatabaseService;
use App\Repositories\PassageReferenceRepository;
use Exception;
use InvalidArgumentException;

/**
 * Factory for creating and populating Bible Study Reference Models.
 */
class BibleStudyReferenceFactory
{
    private DatabaseService $databaseService;
    private PassageReferenceRepository $passageReferenceRepository;

    /**
     * Constructor to inject dependencies.
     *
     * @param DatabaseService $databaseService Database service instance.
     * @param PassageReferenceRepository $passageReferenceRepository 
     *        Repository for passage references.
     */
    public function __construct(
        DatabaseService $databaseService, 
        PassageReferenceRepository $passageReferenceRepository
    ) {
        $this->databaseService = $databaseService;
        $this->passageReferenceRepository = $passageReferenceRepository;
    }

    /**
     * Creates a study reference model based on the study type.
     *
     * @param string $study The type of study ('dbs', 'principle', 'leader').
     * @param int $lesson The lesson identifier.
     * @return mixed The created reference model.
     * @throws Exception If the study type is invalid or data is missing.
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
     * @throws Exception If no data is found for the lesson.
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
        $missing = $this->validatePassageData($result);
        if ($missing) {
            $result = $this->populateMissingValues($result);
            $this->updateStudyDatabase('study_dbs_references', $lesson, $result);
        }
        return (new DbsReferenceModel())->populate($result);
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
            $reference['bookNumber'] = 0;
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

    /**
     * Validates required passage data fields.
     *
     * @param array $data The passage data.
     * @return array Missing fields.
     */
    function validatePassageData(array $data): array
    {
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

    /**
     * Populates missing values in passage data.
     *
     * @param array $data The passage data.
     * @return array The updated data.
     */
    function populateMissingValues(array $data): array
    {
        if ($data['bookID'] == null) {
            $data['bookID'] = $this->passageReferenceRepository
                ->findBookID($data['bookName']);
        }
        if ($data['bookNumber'] == 0) {
            $data['bookNumber'] = $this->passageReferenceRepository
                ->findBookNumber($data['bookID']);
        }
        if ($data['uversionBookID'] == 0) {
            $data['uversionBookID'] = $this->passageReferenceRepository
                ->findUversionBookID($data['bookID']);
        }
        return $data;
    }

    /**
     * Updates the study database with expanded passage information.
     *
     * @param string $studyTable The name of the study table.
     * @param int $lesson The lesson identifier.
     * @param array $data The updated passage data.
     */
    function updateStudyDatabase(
        string $studyTable, 
        int $lesson, 
        array $data
    ) {
        $result = $this->buildPassageReferenceInfoJson($data);
        $this->savePassageReferenceInfo($studyTable, $lesson, $result);
    }

    /**
     * Constructs a JSON string for passage_reference_info.
     *
     * @param array $data The passage data.
     * @return string JSON representation.
     */
    function buildPassageReferenceInfoJson(array $data): string
    {
        return json_encode([
            'bookID' => $data['bookID'] ?? null,
            'bookName' => $data['bookName'] ?? null,
            'bookNumber' => $data['bookNumber'] ?? 0,
            'chapterStart' => $data['chapterStart'] ?? null,
            'chapterEnd' => $data['chapterEnd'] ?? null,
            'verseStart' => $data['verseStart'] ?? null,
            'verseEnd' => $data['verseEnd'] ?? null,
            'passageID' => $data['passageID'] ?? null,
            'uversionBookID' => $data['uversionBookID'] ?? null,
        ]);
    }

    /**
     * Saves the passage reference information to the database.
     *
     * @param string $studyTable The name of the study table.
     * @param int $lesson The lesson identifier.
     * @param string $passageReferenceInfo JSON representation of the info.
     * @throws InvalidArgumentException If the table name is invalid.
     */
    function savePassageReferenceInfo(
        string $studyTable, 
        int $lesson, 
        string $passageReferenceInfo
    ) {
        if (!preg_match('/^[a-zA-Z0-9_]+$/', $studyTable)) {
            throw new InvalidArgumentException('Invalid table name.');
        }

        $query = "UPDATE $studyTable 
                  SET passage_reference_info = :passage_reference_info
                  WHERE lesson = :lesson";
        $params = [
            ':passage_reference_info' => $passageReferenceInfo,
            ':lesson' => $lesson,
        ];
        $this->databaseService->executeQuery($query, $params);
    }
}
