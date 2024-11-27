<?php

namespace App\Factories;

use App\Models\Language\LanguageModel;
use App\Services\Database\DatabaseService;

/**
 * Factory for creating and populating LanguageModel instances.
 */
class LanguageFactory
{
    private $databaseService;

    public function __construct(DatabaseService $databaseService)
    {
        $this->databaseService = $databaseService;
    }

    /**
     * Creates a LanguageModel and populates it with provided data.
     */
    public function create(array $data): LanguageModel
    {
        $model = new LanguageModel();
        $model->populate($data);
        return $model;
    }

    /**
     * Finds a LanguageModel by a specific source code.
     */
    public function findOneByCode(
        string $source,
        string $code
    ): ?LanguageModel {
        $field = 'languageCode' . $source;
        $query = 'SELECT * FROM hl_languages WHERE ' . $field . ' = :id';
        $data = $this->databaseService->fetchRow($query, [':id' => $code]);
        return $data ? $this->create($data) : null;
    }

    /**
     * Finds a LanguageModel by its HL code.
     */
    public function findOneLanguageByLanguageCodeHL(
        string $code
    ): ?LanguageModel {
        $query = 'SELECT * FROM hl_languages WHERE languageCodeHL = :id';
        $data = $this->databaseService->fetchRow($query, [':id' => $code]);
        return $data ? $this->create($data) : null;
    }
}
