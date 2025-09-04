<?php

namespace App\Cron;

use App\Services\Database\DatabaseService;
use App\Services\Language\TranslationBatchService;
use App\Services\LoggerService;
use App\Configuration\Config;
use PDO;
use PDOException;

class TranslationQueueProcessor
{
    protected DatabaseService $db;
    protected TranslationBatchService $translator;

    public function __construct()
    {
        // --- Explicitly load .env.* via Config; this is cron-friendly and
        //     fails fast if your config file is missing or malformed.
        try {
            Config::initialize(); // chooses .env.remote.php when APP_ENV=remote
        } catch (\Throwable $e) {
            LoggerService::logError('Config init failed', $e->getMessage());
            throw $e; // don't proceed without config
        }

        // --- Optional: log what environment we think we're in (handy in cron)
        $env = getenv('APP_ENV') ?: '(unset)';
        LoggerService::logInfo('cron bootstrap', "APP_ENV={$env}");

        // --- Optional: sanity check that the expected DB profile exists.
        //     Using a default (null) to avoid exceptions here; we throw our own
        //     clearer message if keys we need are missing.
        $dbProfile = Config::get('databases.standard', null);
        if (
            !is_array($dbProfile) ||
            empty($dbProfile['DB_HOST']) ||
            empty($dbProfile['DB_DATABASE']) ||
            !array_key_exists('DB_USERNAME', $dbProfile)
        ) {
            LoggerService::logError(
                'DB profile missing',
                'databases.standard must define DB_HOST, DB_DATABASE, DB_USERNAME'
            );
            throw new \RuntimeException('Invalid databases.standard config.');
        }

        // --- Create the DB service; its constructor calls connect().
        //     Our DatabaseService::getPdo() guarantees a live PDO (or throws).
        $this->db = new DatabaseService('standard');

        // --- If your TranslationBatchService needs the DB, inject it here;
        //     otherwise construct as you already do.
        $this->translator = new TranslationBatchService(/* $this->db, ... */);
    }

    // For HTTP webhook use your existing runIfAuthorized() if you like.
    // For cron/CLI, call runCron() directly (see cron lines below).

    public function runCron(int $maxSeconds = 55, int $batchSize = 120): void
    {
        $start = time();
        $worker = gethostname() . '-' . getmypid();

        while ((time() - $start) < $maxSeconds) {
            $claimed = $this->claimBatch($worker, $batchSize);
            if ($claimed === 0) {
                usleep(150000); // idle 150 ms
                continue;
            }

            $items = $this->fetchClaimed($worker);
            if (!$items) continue;

            // Build lang => list of rows (each row has id, text)
            $byLang = [];
            foreach ($items as $row) {
                $byLang[$row['target_lang']][] = [
                    'id'   => (int)$row['id'],
                    'text' => $row['source_text'],
                ];
            }

            foreach ($byLang as $lang => $rows) {
                // Filter out non-translateable rows (same logic as before)
                $kept = [];
                foreach ($rows as $r) {
                    $t = trim($r['text']);
                    if ($t === '') continue;
                    if (preg_match(
                        '/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\+\d{2}:\d{2}|Z)?$/',
                        $t
                    )) continue;
                    if (is_numeric($t)) continue;
                    $kept[] = $r;
                }

                if (empty($kept)) {
                    // Nothing valid in this language bucket
                    $this->releaseAsDone(array_column($rows, 'id'));
                    continue;
                }

                $texts = array_map(fn($r) => $r['text'], $kept);

                try {
                    $translated = $this->translator->translateBatch($texts, $lang);
                } catch (\Throwable $e) {
                    LoggerService::logError(
                        'TranslationQueueProcessor-translateBatch',
                        $e->getMessage()
                    );
                    $this->requeueWithDelay(array_column($rows, 'id'), 15);
                    continue;
                }

                $idsToFinish = [];
                foreach ($kept as $i => $row) {
                    $orig = $row['text'];
                    $tran = $translated[$i] ?? '';

                    if (trim($tran) !== '' && $tran !== $orig) {
                        $this->storeTranslation($lang, $orig, $tran);
                    }
                    // Either translated or intentionally skipped - job is finished
                    $idsToFinish[] = $row['id'];
                }

                $this->releaseAsDone($idsToFinish);
            }
        }

        $this->db->closeConnection();
    }

    protected function claimBatch(string $worker, int $limit): int
    {
        // Atomically lease a batch to this worker
        $sql = "UPDATE translation_queue
                SET locked_by = :w, locked_at = NOW(), status = 'processing'
                WHERE id IN (
                    SELECT id FROM (
                        SELECT id
                        FROM translation_queue
                        WHERE status = 'queued'
                          AND run_after <= NOW()
                        ORDER BY priority DESC, id ASC
                        LIMIT :lim
                    ) AS t
                )";
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
            "SELECT id, target_lang, source_text
             FROM translation_queue
             WHERE status = 'processing' AND locked_by = :w
             ORDER BY id ASC",
            [':w' => $worker]
        );
    }

    protected function releaseAsDone(array $ids): void
    {
        if (!$ids) return;
        $in = implode(',', array_fill(0, count($ids), '?'));
        $this->db->executeQuery(
            "DELETE FROM translation_queue WHERE id IN ($in)",
            $ids
        );
    }

    protected function requeueWithDelay(array $ids, int $seconds): void
    {
        if (!$ids) return;
        $in = implode(',', array_fill(0, count($ids), '?'));
        $params = $ids;
        // Add delay, clear lease, increment attempts
        $this->db->executeQuery(
            "UPDATE translation_queue
             SET status = 'queued',
                 locked_by = NULL,
                 locked_at = NULL,
                 attempts = attempts + 1,
                 run_after = DATE_ADD(NOW(), INTERVAL $seconds SECOND)
             WHERE id IN ($in)",
            $params
        );
    }

    protected function storeTranslation(
        string $langCode,
        string $original,
        string $translated
    ): void {
        LoggerService::logInfo(
            'TranslationQueueProcessor-Store',
            "storing $langCode   $original"
        );

        $this->db->executeQuery(
            "INSERT IGNORE INTO translation_memory
             (source_text, source_lang, target_lang, translated_text)
             VALUES (:s, 'en', :t, :tr)",
            [
                ':s'  => $original,
                ':t'  => $langCode,
                ':tr' => $translated,
            ]
        );
    }
}
