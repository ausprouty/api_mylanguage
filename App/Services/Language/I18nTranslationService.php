<?php

declare(strict_types=1);
namespace App\Services\Language;

use App\Contracts\Translation\TranslationService as TranslationServiceContract;
use App\Repositories\I18nStringsRepository;
use App\Repositories\I18nTranslationsRepository;
use App\Repositories\I18nClientsRepository;
use App\Repositories\I18nResourcesRepository;
// adjust these two if your names/namespaces differ
use App\Services\Database\DatabaseService;
use App\Repositories\LanguageRepository;

class I18nTranslationService implements TranslationServiceContract
{
    public function __construct(
        private I18nStringsRepository       $strings,
        private I18nTranslationsRepository  $translations,
        private I18nClientsRepository       $clients,
        private I18nResourcesRepository     $resources,
        private DatabaseService             $db,
        private LanguageRepository          $languages,
        private string                      $baseLanguage = 'eng00'
    ) {}

    public function baseLanguage(): string
    {
        return $this->baseLanguage;
    }

   public function translateBundle(
        array $bundle,
        string $languageCodeHL,
        ?string $variant,
        array $ctx = []
    ): array {
        // Required context from resolver
        $type            = (string)($ctx['kind']             ?? 'interface');          // i18n_resources.type
        $resourceSubject = (string)($ctx['resourceSubject']  ?? 'app');                // i18n_resources.subject
        $resourceVariant = (string)($ctx['resourceVariant']  ?? ($variant ?: 'default')); // i18n_resources.variant
        $clientCode      = (string)($ctx['clientCode']       ?? 'wsu');                // i18n_clients.clientCode
        $isBase          = (bool)  ($ctx['isBase']           ?? ($languageCodeHL === $this->baseLanguage));
        $normVariant     = (string)($ctx['variant']          ?? ($variant ?: 'default'));

        // Resolve IDs
        $clientId = $this->clientsRepo->getIdByCode($clientCode);
        if (!$clientId) {
            throw new \RuntimeException("Unknown clientCode '{$clientCode}'");
        }
        $resourceId = $this->resourcesRepo->getIdByTypeSubjectVariant($type, $resourceSubject, $resourceVariant);
        if (!$resourceId) {
            throw new \RuntimeException("Unknown resource {$type}/{$resourceSubject}/{$resourceVariant}");
        }

        // Extract master texts and compute deterministic key hashes.
        // Your repo can decide: use key-paths or english text as the hash basis.
        // This call should return an array like:
        //   [
        //     ['key' => 'interface.share.button', 'text' => 'Share this with a friend', 'note' => 'Button label'],
        //     ...
        //   ]
        $masters = $this->extractMasterTexts($bundle);

        // Upsert strings and get back stringIds keyed by hash
        // Repo contract suggestion:
        //   ensureIdsForMasterTexts(int $clientId, int $resourceId, array $masters): array
        // Return: ['<keyHash>' => <stringId>, ...]
        $stringMap = $this->stringsRepo->ensureIdsForMasterTexts(
            clientId:   (int)$clientId,
            resourceId: (int)$resourceId,
            masters:    $masters
        );

        if ($isBase) {
            // For ENG: we've seeded stringIds already; return bundle as-is
            return $bundle;
        }

        // Fetch translations by stringId + HL language
        $stringIds = array_values($stringMap);
        if (empty($stringIds)) {
            return $bundle;
        }

        $rows = $this->translationsRepo->fetchByStringIdsAndLanguage(
            stringIds: $stringIds,
            language:  $languageCodeHL
        );

        // Map stringId -> translatedText
        $trById = [];
        foreach ($rows as $r) {
            $sid = (int)$r['stringId'];
            $trById[$sid] = (string)$r['translatedText'];
        }

        // Apply translations back onto the bundle nodes using the same hash mapping
        // Implement applyTranslationsByStringId() to walk the bundle tree and replace
        // where a hash (or key) maps to a stringId with a translated text.
        $out = $this->applyTranslationsByStringId($bundle, $stringMap, $trById);

        // Optional: annotate meta for debugging
        if (isset($out['meta']) && is_array($out['meta'])) {
            $out['meta']['resourceSubject'] = $resourceSubject;
            $out['meta']['resourceVariant'] = $resourceVariant;
            $out['meta']['clientCode']      = $clientCode;
            $out['meta']['langHL']          = $languageCodeHL;
            $out['meta']['variant']         = $normVariant;
        }

        return $out;
    }

    // -------- internals --------

    private function collectStrings(array $node, array $path, array $skipTopKeys, array &$out): void
    {
        if (empty($path) && is_array($node)) {
            foreach ($skipTopKeys as $skip) unset($node[$skip]);
        }
        if (is_array($node)) {
            foreach ($node as $k => $v) {
                $p = [...$path, (string)$k];
                if (is_array($v)) {
                    $this->collectStrings($v, $p, $skipTopKeys, $out);
                } elseif (is_string($v)) {
                    if ($this->looksHumanText($p, $v)) $out[] = ['path' => $p, 'text' => $v];
                }
            }
        }
    }

    private function looksHumanText(array $path, string $text): bool
    {
        if ($text === '' || trim($text) === '') return false;
        $last = strtolower((string)end($path));
        if (str_contains($last, 'code') || str_contains($last, 'id')) return false;
        return true;
    }

    private function setByPath(array &$arr, array $path, string $value): void
    {
        $ref =& $arr;
        $n = count($path);
        for ($i = 0; $i < $n - 1; $i++) {
            $k = $path[$i];
            if (!isset($ref[$k]) || !is_array($ref[$k])) $ref[$k] = [];
            $ref =& $ref[$k];
        }
        $ref[$path[$n - 1]] = $value;
    }

    private function enqueueMissing(
        string $clientCode,
        string $resourceType,
        string $subject,
        string $variant,
        string $stringKey,
        string $sourceKeyHash,
        ?int   $sourceStringId,
        string $sourceLanguageIso,
        string $targetLanguageIso,
        string $sourceText,
        int    $priority = 0
    ): void {
        $sql = "
        INSERT INTO i18n_translation_queue
          (sourceStringId, sourceLanguageCodeIso, clientCode,
           resourceType, subject, variant, stringKey, sourceKeyHash,
           targetLanguageCodeIso, sourceText, status, runAfter, priority)
        VALUES
          (:sid, :srcIso, :client, :rtype, :subj, :var, :skey, :shash,
           :tIso, :stext, 'queued', NOW(), :prio)
        ON DUPLICATE KEY UPDATE
          runAfter = LEAST(i18n_translation_queue.runAfter, VALUES(runAfter)),
          priority = LEAST(i18n_translation_queue.priority, VALUES(priority))
        ";
        $this->db->executeQuery($sql, [
            ':sid'    => $sourceStringId,
            ':srcIso' => $sourceLanguageIso,
            ':client' => $clientCode,
            ':rtype'  => $resourceType,
            ':subj'   => $subject,
            ':var'    => $variant,
            ':skey'   => $stringKey,
            ':shash'  => $sourceKeyHash,
            ':tIso'   => $targetLanguageIso,
            ':stext'  => $sourceText,
            ':prio'   => $priority,
        ]);
    }
}
