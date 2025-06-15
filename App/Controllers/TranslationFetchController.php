<?php

namespace App\Controllers;

use App\Services\Language\TranslationService;
use App\Utilities\JsonResponse;
use Exception;

/**
 * Controller to handle fetching of translation data for interface and common content.
 */
class TranslationFetchController
{
    /**
     * @var TranslationService Service used to fetch translation data.
     */
    private TranslationService $translationService;

    /**
     * TranslationFetchController constructor.
     *
     * @param TranslationService $translationService The service handling translation logic.
     */
    public function __construct(TranslationService $translationService)
    {
        $this->translationService = $translationService;
    }

    /**
     * Fetches common content translation for a given study and language.
     *
     * Expected keys in $args:
     * - 'study' (string): The identifier for the study (required)
     * - 'languageCodeHL' (string): The language code in HL format (required)
     * - 'logic' (string|null): Optional logic variant to apply
     *
     * Responds with:
     * - JSON success response with translation data
     * - JSON error response if required parameters are missing or an exception is thrown
     *
     * @param array $args Associative array of request parameters.
     * @return void
     */
    public function webFetchCommonContent(array $args): void
    {
        try {
            if (!isset($args['study'], $args['languageCodeHL'])) {
                JsonResponse::error('Missing required arguments: study or languageCodeHL');
            }

            $study = $args['study'];
            $languageCodeHL = $args['languageCodeHL'];

            $output = $this->translationService->getTranslatedContent('commonContent', $study,$languageCodeHL);
            JsonResponse::success($output);
        } catch (Exception $e) {
            JsonResponse::error($e->getMessage());
        }
    }

    /**
     * Fetches interface translation data for a specific app and language.
     *
     * Expected keys in $args:
     * - 'app' (string): The application identifier (required)
     * - 'languageCodeHL' (string): The language code in HL format (required)
     *
     * Responds with:
     * - JSON success response with translation data
     * - JSON error response if required parameters are missing or an exception is thrown
     *
     * @param array $args Associative array of request parameters.
     * @return void
     */
    public function webFetchInterface(array $args): void
    {
        try {
            if (!isset($args['app'], $args['languageCodeHL']) || empty($args['app']) || empty($args['languageCodeHL'])) {
                JsonResponse::error('Missing required arguments: app or languageCodeHL');
            }

            $app = $args['app'];
            $languageCodeHL = $args['languageCodeHL'];

            $output = $this->translationService->getTranslatedContent('interface', $app,$languageCodeHL);
            JsonResponse::success($output);
        } catch (Exception $e) {
            JsonResponse::error($e->getMessage());
        }
    }
}
