<?php
declare(strict_types=1);

namespace App\Cron;
use App\Contracts\Translation\TranslationProvider as ProviderContract;
use App\Services\Database\DatabaseService;
use App\Services\LoggerService;
use DateInterval;
use DateTimeImmutable;
use Exception;
use PDO;
use PDOException;

/**
 * TranslationQueueProcessor
 *
 * Cron-safe worker for i18n_translation_queue:
 * - Picks eligible rows by priority and runAfter
 * - Locks atomically (statuslockedBylockedAt)
 * - Calls a TranslationProvider to obtain MT text
 * - Upserts into i18n_translations (UNIQUE by (stringId, language))
 * - Deletes the queue row on success
 * - On failure, increments attempts and sets exponential backoff
 *
 * Tables:
 *  - i18n_translation_queue
 *    (id, sourceStringId, sourceLanguageCodeGoogle, clientCode,
 *     resourceType, subject, variant, stringKey, sourceKeyHash,
 *     targetLanguageCodeGoogle, sourceText, status, lockedBy, lockedAt,
 *     attempts, runAfter, priority, queuedAt)
 *
 *  - i18n_translations
 *    (translationId, stringId, languageCodeGoogle, translatedText,
 *     status, source, translator, reviewedBy, posted, createdAt, updatedAt)
 */
final class TranslationQueueProcessor
{
    /** @var int */
    private $batchSize = 25;

    /** @var int max attempts before marking failed permanently */
    private $maxAttempts = 6;

    /** @var string ISO 8601 backoff base (2^n minutes) */
    private $backoffUnit = 'PT1M';

    /** @var string */
    private $workerId;

    public function __construct(
        private DatabaseService $db,
        private LoggerService $logger,
        private ProviderContract $translator
    ) {
        // Keep worker id short for UNIQUE index lengths.
        $host = php_uname('n');
        $pid  = (string) getmypid();
        $this->workerId = substr("cron:$host:$pid", 0, 64);
    }

    /**
     * Run one cron tick: pick and process up to $batchSize items.
     */
    public function runOnce(): void
    {
        $started = microtime(true);
        try {
            $jobs = $this->lockBatch();
        } catch (Throwable $e) {
            $this->logger::logError('TQP lockBatch failed', [
                'err' => $e->getMessage(),
            ]);
            return;
        }

        if (!$jobs) {
            $this->logger::logInfo(' TranslationQueProcessor-77', 'TQP: no eligible jobs.');
            return;
        }

        foreach ($jobs as $job) {
            $this->processOne($job);
        }

        $elapsedMs = (int) ((microtime(true) - $started) * 1000);
        $this->logger::logInfo('TQP: batch complete', [
            'picked'  => count($jobs),
            'elapsed' => $elapsedMs . 'ms',
        ]);
    }

    /**
     * Atomically claim a batch:
     *  - status = queued
     *  - runAfter <= now
     *  - oldest first by (priority desc, id asc)
     * Converts to status=processing and sets lockedBy/lockedAt.
     *
     * Uses a two-step approach for broad MariaDB compatibility.
     *
     * @return array<int, array<string,mixed>>
     */
    private function lockBatch(): array
    {
        $pdo = $this->pdo();
        $pdo->beginTransaction();
        try {
            // Step 1: read candidate ids
            $sel = $pdo->prepare(
                'SELECT id
                   FROM i18n_translation_queue
                  WHERE status = "queued"
                    AND runAfter <= UTC_TIMESTAMP()
                    AND (lockedAt IS NULL
                         OR lockedAt < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 10 MINUTE))
               ORDER BY priority DESC, id ASC
                  LIMIT :lim'
            );
            $sel->bindValue(':lim', $this->batchSize, PDO::PARAM_INT);
            $sel->execute();
            $ids = $sel->fetchAll(PDO::FETCH_COLUMN, 0);

            if (!$ids) {
                $pdo->commit();
                return [];
            }

            // Step 2: attempt to lock those ids atomically
            $in  = implode(',', array_fill(0, count($ids), '?'));
            $upd = $pdo->prepare(
                "UPDATE i18n_translation_queue
                    SET status = 'processing',
                        lockedBy = ?,
                        lockedAt = UTC_TIMESTAMP()
                  WHERE id IN ($in)
                    AND status = 'queued'
                    AND runAfter <= UTC_TIMESTAMP()
                    AND (lockedAt IS NULL
                         OR lockedAt < DATE_SUB(UTC_TIMESTAMP(), INTERVAL 10 MINUTE))"
            );

            $bind = [$this->workerId];
            foreach ($ids as $id) {
                $bind[] = (int) $id;
            }
            $upd->execute($bind);

            // Step 3: fetch what we actually locked
            $sel2 = $pdo->prepare(
                "SELECT *
                   FROM i18n_translation_queue
                  WHERE id IN ($in)
                    AND status = 'processing'
                    AND lockedBy = ?"
            );
            $bind2 = [];
            foreach ($ids as $id) {
                $bind2[] = (int) $id;
            }
            $bind2[] = $this->workerId;
            $sel2->execute($bind2);
            $jobs = $sel2->fetchAll(PDO::FETCH_ASSOC);

            $pdo->commit();
            return $jobs ?: [];
        } catch (PDOException $e) {
            $pdo->rollBack();
            throw $e;
        }
    }
    

    public function setTranslator(ProviderContract $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Process a single locked  row.
     * /**
    * @param array{
    *   id:int,
    *   sourceStringId:int|null,
    *   sourceLanguageCodeGoogle:string,
    *   targetLanguageCodeGoogle:string,
    *   clientCode:string,
    *   resourceType:string,
    *   subject:string,
    *   variant:string,
    *   stringKey:string,
    *   sourceKeyHash:string,
    *   sourceText:string,
    *   status:string,
    *   attempts:int,
    *   runAfter:string,
    *   priority:int,
    *   lockedBy?:string|null,
    *   lockedAt?:string|null
    * } $row
    *
     *
     * @param array<string,mixed> $row
     */
    private function processOne(array $row): void
    {
        $this->logger::logDebug('TranslationQueueProceessor-206', $row);
        $id     = (int) $row['id'];
        $this->logger::logDebug('TranslationQueueProceessor-208', $id);
        $sourceLang = $row['sourceLanguageCodeGoogle'] ?: 'en';
        $targetLang = $row['targetLanguageCodeGoogle'];
        $sourceText = (string)$row['sourceText'];

        $stringId = (int)($row['sourceStringId'] ?? 0);
        if ($stringId === 0) {
            $stringId = $this->ensureStringId($row);
        }

        // Guard: need a target and text; sourceStringId is required for the
        // translations table schema.
        if ($stringId === null || $targetLang === '' ||  $sourceText === '') {
            $this->failPermanently($id, 'invalid-queue-row');
            return;
        }

        [$ok, $out, $httpCode, $errMsg, $respLen] = 
           $this->translator->translate(
                [$sourceText],                              // inputs
                $targetLang,                                // target language
                $sourceLang,                                // source language
                 \App\Contracts\Translation\TranslationProvider::FORMAT_TEXT 
            );
            // --- provider call (replace this with however you already call it) ---


        // Normalize result
        $translatedText = is_string($out) ? trim($out) : '';

        // Decide outcome
        $success = ($ok === true)
            && $httpCode >= 200 && $httpCode < 300
            && $translatedText !== '';

        if ($success) {
            $this->logger->logInfo('TQ-success', [
                'stringId'  => $stringId,
                'lang'      => $targetLang,
                'len'       => mb_strlen($translatedText),
                'http'      => $httpCode,
                'jobId'     => $id,
            ]);

            $this->upsertTranslation(
                $stringId,
                $targetLang,
                $translatedText,   // guaranteed string
                'mt',
                'cron:' . $this->workerId
            );

            $this->deleteQueueRow($id);
            $this->logger->logInfo('TQ-acked', ['id' => $id]);
            return;
        }

        // Not a success → classify and handle
        $transient = $this->isTransientFailure($httpCode, $errMsg);

        $diag = [
            'jobId'     => $id,
            'stringId'  => $stringId,
            'lang'      => $targetLang,
            'http'      => $httpCode,
            'ok'        => $ok,
            'err'       => $errMsg,
            'respLen'   => $respLen,
            'outType'   => gettype($out),
            'outSample' => is_string($out) ? mb_substr($out, 0, 80) : null,
        ];

        if ($transient) {
            $this->logger->logWarning('TQ-retry', $diag);
            $this->requeueWithBackoff($job);
            return;
        }

        // Permanent failure → dead-letter (or mark failed without retry)
        $this->logger->logError('TQ-dead', $diag);
        $this->deadLetter($job, $diag);
        return;

    }

    private function isTransientFailure(int $http, ?string $err): bool
    {
        if ($http === 0) return true;           // network/transport
        if ($http === 408) return true;         // request timeout
        if ($http === 429) return true;         // rate limited
        if ($http >= 500) return true;          // server errors
        if ($err && preg_match('/timeout|temporar|reset|quota|rate/i', $err)) {
            return true;
        }
        return false; // everything else treat as permanent
    }


    private function upsertTranslation(
        int $stringId,
        string $languageCodeGoogle,
        string $translatedText,
        string $source,
        string $translator
    ): void {
        $pdo = $this->pdo();
        $pdo = $this->pdo();

        $sql = <<<SQL
            INSERT INTO i18n_translations
            (stringId, languageCodeGoogle, translatedText, status, source, translator, posted)
            VALUES
            (:sid,     :lang,              :txt,            :status, :src,   :who,       UTC_TIMESTAMP())
            ON DUPLICATE KEY UPDATE
            translatedText = VALUES(translatedText),
            status         = VALUES(status),
            source         = VALUES(source),
            translator     = VALUES(translator),
            updatedAt      = UTC_TIMESTAMP()
            SQL;

        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':sid'    => $stringId,
            ':lang'   => $languageCodeGoogle,
            ':txt'    => $translatedText,
            ':status' => 'machine',
            ':src'    => $source,
            ':who'    => $translator,
        ]);
    }

    private function deleteQueueRow(int $id): void
    {
        $pdo = $this->pdo();
        $del = $pdo->prepare(
            'DELETE FROM i18n_translation_queue
              WHERE id = :id AND lockedBy = :who'
        );
        $del->execute([
            ':id'  => $id,
            ':who' => $this->workerId,
        ]);
    }

    /**
     * Increment attempts and set runAfter with exponential backoff.
     * Attempts >= maxAttempts => mark as failed permanently.
     *
     * @param array<string,mixed> $job
     */
    private function requeueWithBackoff(array $job): void
    {
        $id       = (int) $job['id'];
        $attempts = (int) $job['attempts'] + 1;

        if ($attempts >= $this->maxAttempts) {
            $this->failPermanently($id, 'max-attempts');
            return;
        }

        $now  = new DateTimeImmutable('now');
        $base = new DateInterval($this->backoffUnit); // 1 minute
        $mins = 1 << ($attempts - 1); // 1,2,4,8,16,32
        $runAfter = $now->add(
            new DateInterval('PT' . (string) $mins . 'M')
        );

        $pdo = $this->pdo();
        $upd = $pdo->prepare(
            'UPDATE i18n_translation_queue
                SET status   = "queued",
                    attempts = :a,
                    lockedBy = NULL,
                    lockedAt = NULL,
                    runAfter = :ra
              WHERE id = :id'
        );
        $upd->execute([
            ':a'  => $attempts,
            ':ra' => $runAfter->format('Y-m-d H:i:s'),
            ':id' => $id,
        ]);
    }

    private function failPermanently(int $id, string $reason): void
    {
        $pdo = $this->pdo();
        $upd = $pdo->prepare(
            'UPDATE i18n_translation_queue
                SET status   = "failed",
                    lockedBy = NULL,
                    lockedAt = NULL
              WHERE id = :id'
        );
        $upd->execute([':id' => $id]);
        $this->logger::logError('TQP: failed permanently', [
            'id'     => $id,
            'reason' => $reason,
        ]);
    }

    private function pdo(): PDO
    {
        // DatabaseService should expose a PDO with ERRMODE_EXCEPTION
        return $this->db->getPdo();
    }

    public function setBatchSize(int $n): void {
        $this->batchSize = max(1, $n);
    }

    private function ensureStringId(array $row): int
    {
        $this->logger::logInfo('TQP ensureStringId -- 380', $row);
        $pdo = $this->pdo();
        $pdo->beginTransaction();

        try {
            // 0) Resolve FKs from human-friendly fields
            $clientId   = $this->resolveClientId($pdo, (string)$row['clientCode']);
            $this->logger::logInfo('TQP ensureStringId -- 387', $clientId);
            $resourceId = $this->resolveResourceId(
                $pdo,
                (string)$row['resourceType'],
                (string)$row['subject'],
                (string)$row['variant']
            );
            $this->logger::logInfo('TQP ensureStringId -- 395', $resourceId);

            $keyHash     = (string)$row['sourceKeyHash'];  // 40-char sha1
            $englishText = (string)$row['sourceText'];     // queue has the source text

            // 1) Find existing string by (clientId, resourceId, keyHash)
            $sel = $pdo->prepare(
                "SELECT stringId
                FROM i18n_strings
                WHERE clientId = ? AND resourceId = ? AND keyHash = ?"
            );
            $sel->execute([$clientId, $resourceId, $keyHash]);
            $stringId = (int) ($sel->fetchColumn() ?: 0);
            $this->logger::logInfo('TQP ensureStringId -- 407',  $stringId );

            // 2) If not found, insert it
            if ($stringId === 0) {
                $ins = $pdo->prepare(
                    "INSERT INTO i18n_strings
                    (clientId, resourceId, keyHash, englishText, developerNote, isActive, createdAt)
                    VALUES
                    (:cid, :rid, :kh, :en, NULL, 1, CURRENT_TIMESTAMP)"
                );
                $ins->execute([
                    ':cid' => $clientId,
                    ':rid' => $resourceId,
                    ':kh'  => $keyHash,
                    ':en'  => $englishText,
                ]);
                $stringId = (int)$pdo->lastInsertId();
            }

            // 3) Persist back to queue (note: queue table has no updatedAt column)
            $upd = $pdo->prepare(
                "UPDATE i18n_translation_queue
                    SET sourceStringId = :sid
                WHERE id = :qid"
            );
            $upd->execute([
                ':sid' => $stringId,
                ':qid' => (int)$row['id'],
            ]);

            $pdo->commit();

            $this->logger::logInfo('TQP ensureStringId.out', [
                'queueId'  => (int)$row['id'],
                'stringId' => $stringId,
                'clientId' => $clientId,
                'resourceId' => $resourceId,
            ]);

            return $stringId;

        } catch (\Throwable $e) {
            $pdo->rollBack();
            throw $e;
        }
}

/**
 * Resolve or create a clientId from clientCode.
 */
private function resolveClientId(PDO $pdo, string $clientCode): int
{
    // Try existing
    $sel = $pdo->prepare("SELECT clientId FROM i18n_clients WHERE clientCode = ?");
    $sel->execute([$clientCode]);
    $id = (int) ($sel->fetchColumn() ?: 0);
    if ($id > 0) return $id;

    // Insert-or-select for race-safety
    $ins = $pdo->prepare("INSERT IGNORE INTO i18n_clients (clientCode) VALUES (?)");
    $ins->execute([$clientCode]);

    if ($pdo->lastInsertId() !== '0') {
        return (int)$pdo->lastInsertId();
    }

    // Someone else inserted between our SELECT and INSERT
    $sel->execute([$clientCode]);
    $id = (int) ($sel->fetchColumn() ?: 0);
    if ($id > 0) return $id;

    throw new \RuntimeException("Failed to resolve clientId for clientCode={$clientCode}");
}

/*
 * Resolve or create a resourceId from (type, subject, variant).
 * `variant` may be NULL (unique key is on (type, subject, variant)).
 */
private function resolveResourceId(
    PDO $pdo,
    string $type,
    string $subject,
    ?string $variant
): int {
    // SELECT with NULL-safe match for variant
    $sel = $pdo->prepare(
        "SELECT resourceId
           FROM i18n_resources
          WHERE type = ?
            AND subject = ?
            AND ( ( ? IS NULL AND variant IS NULL ) OR variant = ? )"
    );
    $sel->execute([$type, $subject, $variant, $variant]);
    $id = (int)($sel->fetchColumn() ?: 0);
    if ($id > 0) return $id;

    // INSERT (resourceId is auto-increment; createdAt has DEFAULT)
    $ins = $pdo->prepare(
        "INSERT IGNORE INTO i18n_resources (type, subject, variant, description)
         VALUES (?, ?, ?, NULL)"
    );
    $ins->execute([$type, $subject, $variant]);

    // If we inserted, return the new id
    $newId = (int)$pdo->lastInsertId();
    if ($newId > 0) return $newId;

    // Race-safe fallback re-select
    $sel->execute([$type, $subject, $variant, $variant]);
    $id = (int)($sel->fetchColumn() ?: 0);
    if ($id > 0) return $id;

    throw new \RuntimeException("Failed to resolve resourceId for {$type}/{$subject}/" . ($variant ?? 'NULL'));
}



}


