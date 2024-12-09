<?php

namespace App\Factories;

use App\Models\BibleStudy\DbsReferenceModel;
use App\Models\BibleStudy\LeadershipReferenceModel;
use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Services\Database\DatabaseService;
use Exception;

/**
 * Factory for creating and populating Bible Study Reference Models.
 */
class BibleStudyReferenceFactory
{
    private DatabaseService $databaseService;

    /**
     * Constructor to inject the DatabaseService.
     *
     * @param DatabaseService $databaseService The database service instance.
     */
    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
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
        return new DbsReferenceModel($result);
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
        $query = 'SELECT * FROM life_principle_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];
        $data = $this->databaseService->fetchRow($query, $params);

        if (!$data) {
            throw new Exception("No record found for lesson: $lesson");
        }

        $result = $this->expandPassageReferenceInfo($data);
        return new LifePrincipleReferenceModel($result);
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
        $query = 'SELECT * FROM leadership_references WHERE lesson = :lesson';
        $params = [':lesson' => $lesson];
        $data = $this->databaseService->fetchRow($query, $params);

        if (!$data) {
            throw new Exception("No record found for lesson: $lesson");
        }

        $result = $this->expandPassageReferenceInfo($data);
        return new LeadershipReferenceModel($result);
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
            $reference['chapterStart'] = $json['chapterStart'] ?? null;
            $reference['chapterEnd'] = $json['chapterEnd'] ?? null;
            $reference['verseStart'] = $json['verseStart'] ?? null;
            $reference['verseEnd'] = $json['verseEnd'] ?? null;
            $reference['passageID'] = $json['passageID'] ?? null;
            $reference['uversionBookID'] = $json['uversionBookID'] ?? null;
        } else {
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
}
