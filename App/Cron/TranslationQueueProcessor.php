<?php
declare(strict_types=1);

namespace App\Cron;
use App\Contracts\Translation\TranslationProvider;
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
        private TranslationProvider $translator
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
            $this->logger->error('TQP lockBatch failed', [
                'err' => $e->getMessage(),
            ]);
            return;
        }

        if (!$jobs) {
            $this->logger->info('TQP: no eligible jobs.');
            return;
        }

        foreach ($jobs as $job) {
            $this->processOne($job);
        }

        $elapsedMs = (int) ((microtime(true) - $started) * 1000);
        $this->logger->info('TQP: batch complete', [
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
                    AND runAfter <= NOW()
                    AND (lockedAt IS NULL
                         OR lockedAt < DATE_SUB(NOW(), INTERVAL 10 MINUTE))
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
                        lockedAt = NOW()
                  WHERE id IN ($in)
                    AND status = 'queued'
                    AND runAfter <= NOW()
                    AND (lockedAt IS NULL
                         OR lockedAt < DATE_SUB(NOW(), INTERVAL 10 MINUTE))"
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
    

    public function setTranslator(TranslationProvider $translator): void
    {
        $this->translator = $translator;
    }

    /**
     * Process a single locked job row.
     *
     * @param array<string,mixed> $job
     */
    private function processOne(array $job): void
    {
        $id     = (int) $job['id'];
        $srcId  = $job['sourceStringId'] ? (int) $job['sourceStringId'] : null;
        $srcLg  = (string) $job['sourceLanguageCodeGoogle'];
        $tgtLg  = (string) $job['targetLanguageCodeGoogle'];
        $text   = (string) $job['sourceText'];

        // Guard: need a target and text; sourceStringId is required for the
        // translations table schema.
        if ($srcId === null || $tgtLg === '' || $text === '') {
            $this->failPermanently($id, 'invalid-queue-row');
            return;
        }

        try {
            $translated = $this->translator->translate($srcLg, $tgtLg, $text);
            if ($translated === '') {
                throw new Exception('Empty MT result');
            }

            $this->upsertTranslation(
                $srcId,
                $tgtLg,
                $translated,
                'mt',
                'cron:' . $this->workerId
            );

            $this->deleteQueueRow($id);

            $this->logger->info('TQP: ok', [
                'id'     => $id,
                'strId'  => $srcId,
                'tgt'    => $tgtLg,
            ]);
        } catch (Throwable $e) {
            $this->logger->warning('TQP: job failed', [
                'id'    => $id,
                'err'   => $e->getMessage(),
            ]);
            $this->requeueWithBackoff($job);
        }
    }

    private function upsertTranslation(
        int $stringId,
        string $languageCodeGoogle,
        string $translatedText,
        string $source,
        string $translator
    ): void {
        $pdo = $this->pdo();
        $sql = 'INSERT INTO i18n_translations
                   (stringId, languageCodeGoogle, translatedText, status,
                    source, translator, reviewedBy, posted)
                VALUES
                   (:sid, :lang, :txt, :status, :src, :who, NULL, NULL)
                ON DUPLICATE KEY UPDATE
                   translatedText = VALUES(translatedText),
                   status = VALUES(status),
                   source = VALUES(source),
                   translator = VALUES(translator),
                   updatedAt = CURRENT_TIMESTAMP';

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
        $this->logger->error('TQP: failed permanently', [
            'id'     => $id,
            'reason' => $reason,
        ]);
    }

    private function pdo(): PDO
    {
        // DatabaseService should expose a PDO with ERRMODE_EXCEPTION
        return $this->db->getPdo();
    }
}

/**
 * TranslationProvider
 *
 * Minimal interface the cron worker depends on. Provide an implementation
 * (Google, Azure, DeepL, stub, etc.) and bind in DI.
 */
interface TranslationProvider
{
    /**
     * @throws Exception on failure
     */
    public function translate(
        string $sourceLangGoogle,
        string $targetLangGoogle,
        string $text
    ): string;
}
