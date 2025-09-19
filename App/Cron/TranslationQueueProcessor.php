<?php

namespace App\Cron;

use App\Services\Database\DatabaseService;
use App\Services\Language\TranslationBatchService;
use App\Services\LoggerService;
use App\Configuration\Config;
use PDO;

class TranslationQueueProcessor
{
    protected DatabaseService $db;
    protected TranslationBatchService $translator;

    public function __construct()
    {
        Config::initialize(); // loads local/remote env appropriately
        $this->db = new DatabaseService('standard');
        $this->translator = new TranslationBatchService(/* inject deps if needed */);

        $env = getenv('APP_ENV') ?: '(unset)';
        LoggerService::logInfo('cron bootstrap', "APP_ENV={$env}");
    }

    public function runCron(int $maxSeconds = 55, int $batchSize = 120): void
    {
        $start  = time();
        $worker = gethostname() . '-' . getmypid();

        while ((time() - $start) < $maxSeconds) {
            $claimed = $this->claimBatch($worker, $batchSize);
            if ($claimed === 0) {
                usleep(150_000);
                continue;
            }

            $rows = $this->fetchClaimed($worker);
            if (!$rows) {
                continue;
            }

            // Group by target language
            $byLang = [];
            foreach ($rows as $row) {
                $byLang[$row['targetLanguageCodeIso']][] = $row;
            }

            foreach ($byLang as $langIso => $items) {
                // Basic filters (skip blanks, pure timestamps, numeric-only)
                $kept = [];
                foreach ($items as $r) {
                    $text = trim($r['sourceText'] ?? '');
                    if ($text === '') continue;
                    if (preg_match(
                        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:Z|\+\d{2}:\d{2})?$/',
                        $text
                    )) continue;
                    if (is_numeric($text)) continue;
                    $kept[] = $r;
                }

                if (empty($kept)) {
                    $this->releaseAsDone(array_column($items, 'id'));
                    continue;
                }

                $texts = array_map(fn($r) => $r['sourceText'], $kept);

                try {
                    $translated = $this->translator->translateBatch($texts, $langIso);
                } catch (\Throwable $e) {
                    LoggerService::logError('i18nQ-translateBatch', $e->getMessage());
                    $this->requeueWithDelay(array_column($items, 'id'), 15);
                    continue;
                }

                $idsToFinish = [];
                foreach ($kept as $i => $row) {
                    $id       = (int)$row['id'];
                    $orig     = $row['sourceText'];
                    $out      = $translated[$i] ?? '';
                    $attempts = (int)$row['attempts'];

                    if (trim($out) !== '' && $out !== $orig) {
                        // Prefer direct mapping; otherwise resolve by key hash
                        $stringId = $row['sourceStringId']
                            ? (int)$row['sourceStringId']
                            : $this->resolveStringIdByKey(
                                $row['clientCode']   ?? '',
                                $row['resourceType'] ?? '',
                                $row['subject']      ?? '',
                                $row['variant']      ?? '',
                                $row['sourceKeyHash'] ?? ''
                              );

                        if ($stringId) {
                            $this->backfillSourceStringId($id, $stringId);
                            $this->storeTranslation($stringId, $langIso, $out);
                            $idsToFinish[] = $id;
                        } else {
                            // Could not map this job—limited retries, then drop
                            if ($attempts < 3) {
                                $this->requeueWithDelay([$id], 60);
                            } else {
                                LoggerService::logError(
                                    'i18nQ-resolveStringIdByKey',
                                    "Unresolvable queue id {$id} (missing scope or no match)"
                                );
                                $idsToFinish[] = $id; // avoid infinite loop
                            }
                        }
                    } else {
                        // No translation or unchanged → finish
                        $idsToFinish[] = $id;
                    }
                }

                if ($idsToFinish) {
                    $this->releaseAsDone($idsToFinish);
                }
            }
        }

        $this->db->closeConnection();
    }

    protected function claimBatch(string $worker, int $limit): int
    {
        $sql =
            "UPDATE i18n_translation_queue q
                JOIN (
                  SELECT id
                    FROM i18n_translation_queue
                   WHERE status = 'queued'
                     AND runAfter <= NOW()
                     AND (lockedAt IS NULL
                          OR lockedAt < NOW() - INTERVAL 5 MINUTE)
                ORDER BY priority DESC, runAfter ASC, id ASC
                   LIMIT :lim
                ) t ON t.id = q.id
               SET q.status   = 'processing',
                   q.lockedBy = :w,
                   q.lockedAt = NOW()";

        $pdo = $this->db->pdo();
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':w', $worker);
        $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
        $stmt->execute();

        return $stmt->rowCount();
    }

    protected function fetchClaimed(string $worker): array
    {
        return $this->db->fetchAll(
            "SELECT id,
                    targetLanguageCodeIso,
                    sourceText,
                    attempts,
                    priority,
                    runAfter,
                    queuedAt,
                    lockedBy,
                    lockedAt,
                    status,
                    sourceStringId,
                    clientCode,
                    resourceType,
                    subject,
                    variant,
                    stringKey,
                    sourceKeyHash
               FROM i18n_translation_queue
              WHERE status = 'processing' AND lockedBy = :w
           ORDER BY id ASC",
            [':w' => $worker]
        ) ?: [];
    }

    protected function releaseAsDone(array $ids): void
    {
        if (!$ids) return;
        $in = implode(',', array_fill(0, count($ids), '?'));
        $this->db->executeQuery(
            "DELETE FROM i18n_translation_queue WHERE id IN ($in)",
            $ids
        );
    }

    protected function requeueWithDelay(array $ids, int $seconds): void
    {
        if (!$ids) return;
        $in = implode(',', array_fill(0, count($ids), '?'));
        $this->db->executeQuery(
            "UPDATE i18n_translation_queue
                SET status   = 'queued',
                    lockedBy = NULL,
                    lockedAt = NULL,
                    attempts = attempts + 1,
                    runAfter = DATE_ADD(NOW(), INTERVAL {$seconds} SECOND)
              WHERE id IN ($in)",
            $ids
        );
    }

    protected function backfillSourceStringId(int $queueId, int $stringId): void
    {
        $this->db->executeQuery(
            "UPDATE i18n_translation_queue
                SET sourceStringId = :sid
              WHERE id = :id AND sourceStringId IS NULL",
            [':sid' => $stringId, ':id' => $queueId]
        );
    }

    /**
     * Resolve by scope + keyHash (matches i18n_strings.keyHash).
     * Returns stringId or null.
     */
    protected function resolveStringIdByKey(
        string $clientCode,
        string $resourceType,
        string $subject,
        string $variant,
        string $sourceKeyHash
    ): ?int {
        if ($clientCode === '' || $resourceType === '' || $subject === '' || $sourceKeyHash === '') {
            return null;
        }

        $sql = "
            SELECT s.stringId
              FROM i18n_strings   s
              JOIN i18n_resources r ON r.resourceId = s.resourceId
              JOIN i18n_clients   c ON c.clientId   = s.clientId
             WHERE c.clientCode = :c
               AND r.type       = :r
               AND r.subject    = :s
               AND COALESCE(r.variant, '') = COALESCE(NULLIF(:v, ''), '')
               AND s.keyHash    = :h
             LIMIT 1
        ";
        $v = ($variant === null || $variant === 'default') ? '' : (string)$variant;
        $id = $this->db->fetchSingleValue($sql, [
            ':c' => $clientCode,
            ':r' => $resourceType,
            ':s' => $subject,
            ':v' => $v,
            ':h' => $sourceKeyHash, // 40-hex only (no "sha1:" prefix)
        ]);

        return $id ? (int)$id : null;
    }
    //  Add this helper method somewhere in the class (private is fine).
/** Temporary ISO→HL mapping. For now default to ISO if no mapping is known. */
    private function isoToHL(string $iso): string
    {
        // TODO: replace with your real HL mapping table/service.
        // Minimal examples; keep default = ISO so we satisfy NOT NULL.
        return match (strtolower($iso)) {
            'en' => 'eng00',
            // 'gu' => 'guj00',   // uncomment if this is correct in your system
            default => $iso,
        };
    }

    protected function storeTranslation(int $stringId, string $langIso, string $text): void
{
    // 1) Try UPDATE by languageCodeIso
    $stmt = $this->db->executeQuery(
        "UPDATE i18n_translations
            SET translatedText = :txt,
                status         = 'machine',
                source         = 'mt',
                translator     = 'queue',
                updatedAt      = NOW()
          WHERE stringId        = :sid
            AND languageCodeIso = :iso",
        [
            ':txt' => $text,
            ':sid' => $stringId,
            ':iso' => $langIso,
        ]
    );
    $rows = $stmt ? $stmt->rowCount() : 0;
    if ($rows > 0) {
        return;
    }

    // 2) Try UPDATE by languageCodeHL (derived)
    $langHL = $this->isoToHL($langIso);
    $stmt = $this->db->executeQuery(
        "UPDATE i18n_translations
            SET translatedText = :txt,
                status         = 'machine',
                source         = 'mt',
                translator     = 'queue',
                updatedAt      = NOW()
          WHERE stringId        = :sid
            AND languageCodeHL  = :hl",
        [
            ':txt' => $text,
            ':sid' => $stringId,
            ':hl'  => $langHL,
        ]
    );
    $rows = $stmt ? $stmt->rowCount() : 0;
    if ($rows > 0) {
        // Optionally backfill languageCodeIso if you want:
        // $this->db->executeQuery("UPDATE i18n_translations SET languageCodeIso=:iso WHERE stringId=:sid AND languageCodeHL=:hl AND languageCodeIso IS NULL", [':iso'=>$langIso, ':sid'=>$stringId, ':hl'=>$langHL]);
        return;
    }

    // 3) No existing row → INSERT a fresh translation
    $this->db->executeQuery(
        "INSERT INTO i18n_translations
            (stringId, languageCodeHL, languageCodeIso,
             translatedText, status, source, translator,
             createdAt, updatedAt)
         VALUES
            (:sid, :hl, :iso,
             :txt, 'machine', 'mt', 'queue',
             NOW(), NOW())",
        [
            ':sid' => $stringId,
            ':hl'  => $langHL,
            ':iso' => $langIso,
            ':txt' => $text,
        ]
    );
  }
   
}
