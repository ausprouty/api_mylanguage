<?php

namespace App\Factories;

use App\Models\BibleStudy\DbsReferenceModel;
use App\Models\BibleStudy\LeadershipReferenceModel;
use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Repositories\PassageReferenceRepository;
use App\Services\Database\DatabaseService;
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
     * @param PassageReferenceRepository $passageReferenceRepository Repository instance.
     */
    public function __construct(
        DatabaseService $databaseService,
        PassageReferenceRepository $passageReferenceRepository
    ) {
        $this->databaseService = $databaseService;
        $this->passageReferenceRepository = $passageReferenceRepository;
    }

    /**
     * Builds a JSON string for passage_reference_info.
     *
     * @param array $data The passage data.
     * @return string JSON representation.
     */
    protected function buildPassageReferenceInfoJson(array $data): string
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
     * Creates a study reference model based on the study type.
     *
     * @param string $study The type of study ('dbs', 'principle', 'leader').
     * @param int $lesson The lesson identifier.
     * @return mixed The created reference model.
     * @throws Exception If the study type is invalid.
     */
    public function createModel(string $study, int $lesson)
    {
        return match ($study) {
            'dbs' => $this->createDbsReferenceModel($lesson),
            'principle' => $this->createLifePrincipleReferenceModel($lesson),
            'leader' => $this->createLeadershipReferenceModel($lesson),
            default => throw new Exception("Invalid study type: $study"),
        };
    }

    /**
     * Creates and populates a DbsReferenceModel.
     *
     * @param int $lesson The lesson identifier.
     * @return DbsReferenceModel The populated model.
     * @throws Exception If no data is found for the lesson.
     */
    protected function createDbsReferenceModel(int $lesson): DbsReferenceModel
    {
        return $this->generateReferenceModel(
            'study_dbs_references',
            $lesson,
            new DbsReferenceModel()
        );
    }

    /**
     * Creates and populates a LeadershipReferenceModel.
     *
     * @param int $lesson The lesson identifier.
     * @return LeadershipReferenceModel The populated model.
     * @throws Exception If no data is found for the lesson.
     */
    protected function createLeadershipReferenceModel(
        int $lesson
    ): LeadershipReferenceModel {
        return $this->generateReferenceModel(
            'study_leadership_references',
            $lesson,
            new LeadershipReferenceModel()
        );
    }

    /**
     * Creates and populates a LifePrincipleReferenceModel.
     *
     * @param int $lesson The lesson identifier.
     * @return LifePrincipleReferenceModel The populated model.
     * @throws Exception If no data is found for the lesson.
     */
    protected function createLifePrincipleReferenceModel(
        int $lesson
    ): LifePrincipleReferenceModel {
        return $this->generateReferenceModel(
            'study_life_principle_references',
            $lesson,
            new LifePrincipleReferenceModel()
        );
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
            return array_merge($reference, $json);
        }

        error_log(
            'Failed to decode passage_reference_info: ' .
            ($reference['passage_reference_info'] ?? '')
        );

        return array_merge($reference, [
            'bookID' => null,
            'bookName' => null,
            'bookNumber' => 0,
            'chapterStart' => null,
            'chapterEnd' => null,
            'verseStart' => null,
            'verseEnd' => null,
            'passageID' => null,
            'uversionBookID' => null,
        ]);
    }

    /**
     * Generates a reference model by fetching and processing data.
     *
     * @param string $table The study table name.
     * @param int $lesson The lesson identifier.
     * @param mixed $model The model instance to populate.
     * @return mixed The populated model.
     */
    private function generateReferenceModel(
        string $table,
        int $lesson,
        mixed $model
    ) {
        $query = "SELECT * FROM $table WHERE lesson = :lesson";
        $params = [':lesson' => $lesson];
        $data = $this->databaseService->fetchRow($query, $params);

        if (!$data) {
            throw new Exception("No record found for lesson: $lesson");
        }

        $result = $this->expandPassageReferenceInfo($data);
        $missing = $this->validatePassageData($result);

        if ($missing) {
            $result = $this->populateMissingValues($result);
            $this->updateStudyDatabase($table, $lesson, $result);
        }

        return $model->populate($result);
    }

    /**
     * Populates missing values in passage data.
     *
     * @param array $data The passage data.
     * @return array The updated data.
     */
    protected function populateMissingValues(array $data): array
    {
        if ($data['bookID'] === null) {
            $data['bookID'] = $this->passageReferenceRepository
                ->findBookID($data['bookName']);
        }
        if ($data['bookNumber'] === 0) {
            $data['bookNumber'] = $this->passageReferenceRepository
                ->findBookNumber($data['bookID']);
        }
        if ($data['uversionBookID'] === 0) {
            $data['uversionBookID'] = $this->passageReferenceRepository
                ->findUversionBookID($data['bookID']);
        }
        return $data;
    }

    /**
     * Saves the passage reference information to the database.
     *
     * @param string $studyTable The name of the study table.
     * @param int $lesson The lesson identifier.
     * @param string $passageReferenceInfo JSON representation of the info.
     * @throws InvalidArgumentException If the table name is invalid.
     */
    protected function savePassageReferenceInfo(
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

    /**
     * Updates the study database with expanded passage information.
     *
     * @param string $studyTable The name of the study table.
     * @param int $lesson The lesson identifier.
     * @param array $data The updated passage data.
     */
    protected function updateStudyDatabase(
        string $studyTable,
        int $lesson,
        array $data
    ) {
        $json = $this->buildPassageReferenceInfoJson($data);
        $this->savePassageReferenceInfo($studyTable, $lesson, $json);
    }

    /**
     * Validates required passage data fields.
     *
     * @param array $data The passage data.
     * @return array Missing fields.
     */
    protected function validatePassageData(array $data): array
    {
        $requiredFields = [
            'entry', 'bookName', 'bookID', 'uversionBookID',
            'bookNumber', 'testament', 'chapterStart', 'verseStart',
            'verseEnd', 'passageID',
        ];

        return array_filter($requiredFields, fn($field) => empty($data[$field]));
    }
}
