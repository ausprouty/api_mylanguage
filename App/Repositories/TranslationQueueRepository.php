<?php

namespace App\Repositories;

use App\Services\Database\DatabaseService;
use App\Services\LoggerService;

class TranslationQueueRepository
{
    public function __construct(private DatabaseService $db) {}

    /**
     * Atomically claim one runnable job.
     * Returns the claimed row or null if none ready.
     */
    public function claimNextJob(string $worker): ?array
    {
        $this->db->beginTransaction();
        try {
            $id = $this->db->fetchSingleValue(
                "SELECT id
                   FROM translation_queue
                  WHERE status = 'queued'
                    AND run_after <= NOW()
               ORDER BY priority DESC, run_after ASC, id ASC
                  LIMIT 1
                  FOR UPDATE SKIP LOCKED"
            );

            if ($id === null) {
                $this->db->commit();
                return null;
            }

            $this->db->executeQuery(
                "UPDATE translation_queue
                    SET status = 'processing',
                        locked_by = :w,
                        locked_at = NOW()
                  WHERE id = :id",
                [':w' => $worker, ':id' => (int)$id]
            );

            $row = $this->db->fetchRow(
                "SELECT id, target_lang, source_text, attempts, priority,
                        run_after, queued_at, locked_by, locked_at, status
                   FROM translation_queue
                  WHERE id = :id",
                [':id' => (int)$id]
            );

            $this->db->commit();
            return $row ?: null;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            LoggerService::logError('claimNextJob', $e->getMessage());
            throw $e;
        }
    }

    /** Mark a job as successfully completed. */
    public function markDone(int $id): void
    {
        $this->db->executeQuery(
            "DELETE FROM translation_queue WHERE id = :id",
            [':id' => $id]
        );
    }

    /**
     * Mark a job failed and apply backoff.
     * If you add an error_message column, we’ll store $error too.
     */
    public function markFailed(
        int $id,
        string $error = '',
        int $delaySeconds = 300
    ): void {
        $sql =
          "UPDATE translation_queue
              SET status = 'queued',
                  attempts = attempts + 1,
                  run_after = NOW() + INTERVAL :s SECOND,
                  locked_by = NULL,
                  locked_at = NULL
            WHERE id = :id";

        $this->db->executeQuery($sql, [':s' => $delaySeconds, ':id' => $id]);

        // Optional: if you add `error_message` VARCHAR(1000)
        // $this->db->executeQuery(
        //   "UPDATE translation_queue
        //       SET error_message = :e
        //     WHERE id = :id",
        //   [':e' => mb_substr($error, 0, 1000), ':id' => $id]
        // );
    }

    /** Requeue stale processing jobs (worker crashed). */
    public function releaseStaleProcessing(int $minutes = 10): int
    {
        $stmt = $this->db->executeQuery(
            "UPDATE translation_queue
                SET status = 'queued',
                    locked_by = NULL,
                    locked_at = NULL,
                    run_after = NOW()
              WHERE status = 'processing'
                AND locked_at < NOW() - INTERVAL :m MINUTE",
            [':m' => $minutes]
        );
        return $stmt ? $stmt->rowCount() : 0;
    }
}
