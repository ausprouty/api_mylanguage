<?php

namespace App\Services\BibleStudy;

use App\Renderers\RendererFactory;
use App\Repositories\LanguageRepository;
use App\Services\ResourceStorageService;
use App\Traits\BibleStudyFileNamingTrait;
use InvalidArgumentException;

class BibleStudyService
{
    use BibleStudyFileNamingTrait;

    private RendererFactory $rendererFactory;
    private LanguageRepository $languageRepository;

    /**
     * Constructor initializes dependencies.
     */
    public function __construct(
        RendererFactory $rendererFactory,
        LanguageRepository $languageRepository
    ) {
        $this->rendererFactory = $rendererFactory;
        $this->languageRepository = $languageRepository;
    }

    /**
     * Retrieves a study file or creates it if not found.
     *
     * @param string $study Study type (e.g., dbs, leadership).
     * @param string $format File format (e.g., html, pdf).
     * @param string $session Session identifier.
     * @param string $languageCodeHL1 Primary language code.
     * @param string|null $languageCodeHL2 Secondary language code, optional.
     * @return string Path to the study file.
     */
    public function getStudy(
        string $study,
        string $format,
        string $session,
        string $languageCodeHL1,
        ?string $languageCodeHL2 = null
    ): string {
        $filename = $this->getFileName(
            $study, $format, $session, $languageCodeHL1, $languageCodeHL2
        );
        $storagePath = $this->getStoragePath($study, $format);
        $storageService = new ResourceStorageService($storagePath);

        $file = $storageService->retrieve($filename);
        return $file 
            ? $file 
            : $this->createStudy(
                $study, $format, $session, $languageCodeHL1, $languageCodeHL2
            );
    }

    /**
     * Creates a new study file.
     *
     * @param string $study Study type.
     * @param string $format File format.
     * @param string $session Session identifier.
     * @param string $languageCodeHL1 Primary language code.
     * @param string|null $languageCodeHL2 Secondary language code, optional.
     * @return string Path to the created study file.
     */
    private function createStudy(
        string $study,
        string $format,
        string $session,
        string $languageCodeHL1,
        ?string $languageCodeHL2 = null
    ): string {
        return $languageCodeHL2
            ? $this->createBilingualStudy(
                $study, $format, $session, $languageCodeHL1, $languageCodeHL2
            )
            : $this->createMonolingualStudy(
                $study, $format, $session, $languageCodeHL1
            );
    }

    /**
     * Creates a mono-lingual study.
     *
     * @param string $study Study type.
     * @param string $format File format.
     * @param string $session Session identifier.
     * @param string $languageCodeHL1 Primary language code.
     * @return string Path to the created mono-lingual study file.
     */
    protected function createMonolingualStudy(
        string $study,
        string $format,
        string $session,
        string $languageCodeHL1
    ): string {
        $studyClass = $this->resolveStudyClass($study, false);
        return (new $studyClass(
            $study, $format, $session, $languageCodeHL1
        ))->generate();
    }

    /**
     * Creates a bilingual study.
     *
     * @param string $study Study type.
     * @param string $format File format.
     * @param string $session Session identifier.
     * @param string $languageCodeHL1 Primary language code.
     * @param string $languageCodeHL2 Secondary language code.
     * @return string Path to the created bi-lingual study file.
     */
    protected function createBilingualStudy(
        string $study,
        string $format,
        string $session,
        string $languageCodeHL1,
        string $languageCodeHL2
    ): string {
        $studyClass = $this->resolveStudyClass($study, true);
        return (new $studyClass(
            $study, $format, $session, $languageCodeHL1, $languageCodeHL2
        ))->generate();
    }

    /**
     * Resolves the appropriate study class based on the type and language mode.
     *
     * @param string $study Study type.
     * @param bool $isBilingual Indicates if the study is bilingual.
     * @return string Fully qualified study class name.
     * @throws InvalidArgumentException If the study type is invalid.
     */
    private function resolveStudyClass(string $study, bool $isBilingual): string
    {
        $studyClasses = [
            'dbs' => $isBilingual ? 'BilingualDBSStudy' : 'MonoLingualDBSStudy',
            'leadership' => $isBilingual
                ? 'BilingualLeadershipStudy'
                : 'MonoLingualLeadershipStudy',
            'principles' => $isBilingual
                ? 'BilingualPrinciplesStudy'
                : 'MonoLingualPrinciplesStudy',
        ];

        if (!isset($studyClasses[$study])) {
            throw new InvalidArgumentException('Unknown study type');
        }

        return "\\App\\Studies\\" . $studyClasses[$study];
    }
}
