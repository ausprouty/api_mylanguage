<?php

namespace App\Factories;

use App\Models\BibleStudy\StudyReferenceModel;
use App\Repositories\PassageReferenceRepository;
use App\Services\Database\DatabaseService;
use Exception;

class BibleStudyReferenceFactory
{
    private DatabaseService $databaseService;
    private PassageReferenceRepository $passageReferenceRepository;

    public function __construct(
        DatabaseService $databaseService,
        PassageReferenceRepository $passageReferenceRepository
    ) {
        $this->databaseService = $databaseService;
        $this->passageReferenceRepository = $passageReferenceRepository;
    }

    public function createModel(string $study, int $lesson): StudyReferenceModel
    {
        $query = "SELECT * FROM study_references WHERE study = :study AND lesson = :lesson";
        $params = [':study' => $study, ':lesson' => $lesson];
        $data = $this->databaseService->fetchRow($query, $params);

        if (!$data) {
            throw new Exception("No record found for study '$study' and lesson $lesson.");
        }

        $result = $this->expandPassageReferenceInfo($data);
        $missing = $this->validatePassageData($result);

        if ($missing) {
            $result = $this->populateMissingValues($result);
            $this->updateStudyDatabase($study, $lesson, $result);
        }

        return (new StudyReferenceModel())->populate($result)->setStudy($study);
    }

    protected function expandPassageReferenceInfo(array $reference): array
    {
        $json = json_decode($reference['passageReferenceInfo'] ?? '', true);

        if (!$json) {
            error_log('Failed to decode passageReferenceInfo: ' .
                ($reference['passageReferenceInfo'] ?? 'NULL') .
                '. Error: ' . json_last_error_msg());
            $json = [];
        }

        return array_merge($reference, [
            'bookID' => $json['bookID'] ?? null,
            'bookName' => $json['bookName'] ?? null,
            'bookNumber' => $json['bookNumber'] ?? 0,
            'chapterStart' => $json['chapterStart'] ?? null,
            'chapterEnd' => $json['chapterEnd'] ?? null,
            'verseStart' => $json['verseStart'] ?? null,
            'verseEnd' => $json['verseEnd'] ?? null,
            'passageID' => $json['passageID'] ?? null,
            'uversionBookID' => $json['uversionBookID'] ?? null,
        ]);
    }

    protected function validatePassageData(array $data): array
    {
        $requiredFields = [
            'bookName', 'bookID', 'uversionBookID', 'bookNumber',
            'testament', 'chapterStart', 'verseStart', 'verseEnd', 'passageID'
        ];

        return array_filter($requiredFields, fn($field) => empty($data[$field]));
    }

    protected function populateMissingValues(array $data): array
    {
        if ($data['bookID'] === null) {
            $data['bookID'] = $this->passageReferenceRepository->findBookID($data['bookName']);
        }
        if ($data['bookNumber'] === 0) {
            $data['bookNumber'] = $this->passageReferenceRepository->findBookNumber($data['bookID']);
        }
        if ($data['uversionBookID'] === null) {
            $data['uversionBookID'] = $this->passageReferenceRepository->findUversionBookID($data['bookID']);
        }
        return $data;
    }

    protected function updateStudyDatabase(string $study, int $lesson, array $data): void
    {
        $json = $this->buildPassageReferenceInfoJson($data);

        $query = "UPDATE study_references
                  SET passageReferenceInfo = :passageReferenceInfo
                  WHERE study = :study AND lesson = :lesson";

        $params = [
            ':passageReferenceInfo' => $json,
            ':study' => $study,
            ':lesson' => $lesson,
        ];

        $this->databaseService->executeQuery($query, $params);
    }

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
}
