<?php

namespace App\Factories;

use App\Models\Bible\BibleModel;
use App\Repositories\BibleRepository;

/**
 * Factory for creating and populating BibleModel instances.
 */
class BibleFactory
{
    private $repository;

    /**
     * Constructor to initialize the repository dependency.
     */
    public function __construct(BibleRepository $repository)
    {
        $this->repository = $repository;
    }

    /**
     * Creates a BibleModel populated from a database row by bid.
     */
    public function createFromBid(int $bid): BibleModel
    {
        $model = new BibleModel($this->repository);
        $data = $this->repository->findBibleByBid($bid);
        if ($data) {
            $model->populate($data);
        }
        return $model;
    }

    /**
     * Creates a BibleModel populated from a language code.
     */
    public function createFromLanguageCodeHL(string $languageCodeHL): BibleModel
    {
        $model = new BibleModel($this->repository);
        $data = $this->repository->findBestBibleByLanguageCodeHL($languageCodeHL);
        if ($data) {
            $model->populate($data);
        }
        return $model;
    }

    /**
     * Creates a BibleModel populated from an external ID.
     */
    public function createFromExternalId(string $externalId): BibleModel
    {
        $model = new BibleModel($this->repository);
        $data = $this->repository->findBibleByExternalId($externalId);
        if ($data) {
            $model->populate($data);
        }
        return $model;
    }

    /**
     * Creates a BibleModel with media type properties set.
     */
    public function createWithMediaType(
        string $type,
        array $audioTypes,
        array $textTypes,
        array $videoTypes
    ): BibleModel {
        $model = new BibleModel($this->repository);
        $model->resetMediaFlags();

        if (in_array($type, $textTypes, true)) {
            $model->setText(true);
        }
        if (in_array($type, $audioTypes, true)) {
            $model->setAudio(true);
        }
        if (in_array($type, $videoTypes, true)) {
            $model->setVideo(true);
        }

        return $model;
    }

    /**
     * Prepares and populates a BibleModel for saving.
     */
    public function createPreparedForSave(
        string $source,
        string $externalId,
        string $volume,
        string $collectionCode,
        string $format
    ): BibleModel {
        $model = new BibleModel($this->repository);
        $model->populate([
            'source' => $source,
            'externalId' => $externalId,
            'volumeName' => $volume,
            'collectionCode' => $collectionCode,
            'dateVerified' => date('Y-m-d'),
            'format' => $format
        ]);

        return $model;
    }
}
