<?php
declare(strict_types=1);

namespace App\Services\Language;

use App\Configuration\Config;
use App\Repositories\LanguageRepository;
use App\Services\Database\DatabaseService;
use App\Services\LoggerService;
use App\Services\Language\TranslationMemoryService;
use App\Contracts\Translation\BundleRepository;

/**
 * Service for loading and translating JSON-based language files dynamically.
 * Automatically translates non-English content using cached memory or queues
 * for future translation.
 */
final class TranslationService
{
    private readonly string $rootTemplates;
    private readonly string $rootTranslations;

    public function __construct(
        private readonly DatabaseService $databaseService,
        private readonly LanguageRepository $languageRepository,
        private readonly TranslationMemoryService $translationMemoryService,
        private readonly BundleRepository $bundles
    ) {
        $this->rootTemplates     = Config::getDir('resources.templates');
        $this->rootTranslations  = Config::getDir('resources.translations');
    }

    /**
     * Returns translated content (or master English if requested).
     *
     * @param string      $type       'commonContent' | 'interface'
     * @param string      $sourceKey  study/app key (e.g. 'hope' or site 'wsu')
     * @param string      $languageHL HL code (e.g. 'eng00', 'urd00')
     * @param string|null $variant    reserved for future overlay logic
     *
     * @return array
     */
    public function getTranslatedContent(
        string $type,
        string $sourceKey,
        string $languageHL,
        ?string $variant = null
    ): array {
        // 1) Load English master (eng00) for this type+sourceKey
        try {
            $masterData = $this->bundles->getMaster(
                $type,
                $sourceKey,
                'eng00',
                $variant
            );
        } catch (\Throwable $e) {
            LoggerService::logError(
                'TranslationService',
                sprintf(
                    'Missing master for type=%s, sourceKey=%s, variant=%s: %s',
                    $type,
                    $sourceKey,
                    $variant ?? 'null',
                    $e->getMessage()
                )
            );
            return [];
        }

        if (strtolower($languageHL) === 'eng00') {
            return $masterData;
        }

        // 2) Translate recursively using memory/queue
        //    Remove English metadata before translation (if present)
        if (isset($masterData['language'])) {
            unset($masterData['language']);
        }

        // HL -> Google code
        $googleCode = $this->languageRepository
            ->getCodeGoogleFromCodeHL($languageHL);

        if (!$googleCode) {
            LoggerService::logError(
                'TranslationService',
                "Missing Google code for $languageHL"
            );
            // Fall back to English content if we can't determine target
            return $masterData;
        }

        [$translatedData, $complete] = $this->translateArrayRecursive(
            data: $masterData,
            targetLang: $googleCode
        );

        // 3) Add metadata to translated output
        $translatedData['language'] = [
            'hlCode'             => $languageHL,
            'google'             => $googleCode,
            'translatedFrom'     => 'eng00',
            'translatedDate'     => date('c'),
            'translationComplete'=> $complete,
            // if master had lastUpdated, keep it; otherwise now
            'lastUpdated'        => $masterData['language']['lastUpdated']
                                    ?? date('c'),
        ];

        // 4) If incomplete, create a cron token and attach
        if ($complete === false) {
            $cronKey = bin2hex(random_bytes(16)); // 32 chars
            $this->databaseService->executeQuery(
                "INSERT INTO cron_tokens (token) VALUES (:token)",
                [':token' => $cronKey]
            );
            $translatedData['language']['cronKey'] = $cronKey;
        }

        return $translatedData;
    }

    /**
     * Recursively translates each string using cached memory or queues it.
     *
     * @param array  $data       English master array
     * @param string $targetLang Google Translate code (e.g. 'ur')
     * @return array [translated array, isComplete flag]
     */
    private function translateArrayRecursive(
        array $data,
        string $targetLang
    ): array {
        $translated = [];
        $complete   = true;

        foreach ($data as $key => $value) {
            if (is_array($value)) {
                [$sub, $ok] = $this->translateArrayRecursive(
                    $value,
                    $targetLang
                );
                $translated[$key] = $sub;
                if (!$ok) {
                    $complete = false;
                }
                continue;
            }

            if (is_string($value) && trim($value) !== '') {
                $cached = $this->translationMemoryService->get(
                    $value,
                    $targetLang
                );
                if ($cached !== null) {
                    $translated[$key] = $cached;
                } else {
                    // not cached -> queue and mark incomplete
                    $complete = false;
                    $this->addToTranslationQueue($targetLang, $value);
                    // fallback to English
                    $translated[$key] = $value;
                }
                continue;
            }

            // passthrough for non-strings/null/empty strings
            $translated[$key] = $value;
        }

        return [$translated, $complete];
    }

    /**
     * Queue a missing string for later translation (idempotent insert).
     * Current target table: translation_queue (target_lang, source_text).
     */
    private function addToTranslationQueue(
        string $targetLang,
        string $sourceText
    ): void {
        $query = "
            INSERT INTO translation_queue (target_lang, source_text)
            SELECT :target_lang1, :source_text1
            WHERE NOT EXISTS (
              SELECT 1
              FROM translation_queue
              WHERE target_lang = :target_lang2
                AND source_text = :source_text2
            )
        ";

        $params = [
            ':target_lang1' => $targetLang,
            ':source_text1' => $sourceText,
            ':target_lang2' => $targetLang,
            ':source_text2' => $sourceText,
        ];

        $this->databaseService->executeQuery($query, $params);
    }

    /** Build a dotted key for nested paths: 'interface.copyLink' etc. */
    private function joinKeyPath(array $parts, string $leaf): string
    {
        $p = array_filter(
            $parts,
            static fn($s) => $s !== '' && $s !== null
        );
        $p[] = $leaf;
        return implode('.', $p);
    }

    /** SHA1 of the English source text for queue de-dupe */
    private function sha1Text(string $s): string
    {
        return sha1($s);
    }

    /** ISO from HL (prefer repository; fallback to DB) */
    private function isoFromHl(string $languageHL): ?string
    {
        // If your LanguageRepository already has this, call that instead:
        $iso = $this->languageRepository->getCodeIsoFromCodeHL($languageHL);
        if ($iso) {
            return $iso;
        }

        $sql = "
            SELECT languageCodeIso
            FROM hl_languages
            WHERE languageCodeHL = :hl
            LIMIT 1
        ";

        /** @var string|null $val */
        $val = $this->databaseService->fetchValue($sql, [':hl' => $languageHL]);
        return $val ?: null;
    }
}
