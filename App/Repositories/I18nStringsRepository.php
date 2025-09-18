<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;

final class I18nStringsRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Ensure each EN master text has a stable stringId for (clientId, resourceId).
     * Returns map: keyHash => stringId. keyHash = sha1(englishText).
     */
    public function ensureIdsForMasterTexts(
        int $clientId,
        int $resourceId,
        array $masters
    ): array {
        $map = [];

        $sql = 'INSERT INTO i18n_strings
                  (clientId, resourceId, keyHash, englishText, developerNote, isActive)
                VALUES (?, ?, ?, ?, ?, 1)
                ON DUPLICATE KEY UPDATE
                  englishText   = VALUES(englishText),
                  developerNote = VALUES(developerNote),
                  updatedAt     = CURRENT_TIMESTAMP,
                  stringId      = LAST_INSERT_ID(stringId)';

        $stmt = $this->pdo->prepare($sql);

        $this->pdo->beginTransaction();
        try {
            foreach ($masters as $m) {
                if (is_string($m)) {
                    $text = $m;
                    $note = null;
                } elseif (is_array($m)) {
                    $text = (string)($m['text'] ?? '');
                    $note = $m['note'] ?? null;
                } else {
                    continue;
                }

                if ($text === '') {
                    continue;
                }

                $hash = sha1($text);

                $stmt->execute([
                    $clientId,
                    $resourceId,
                    $hash,
                    $text,
                    $note,
                ]);

                $sid = (int)$this->pdo->lastInsertId();
                $map[$hash] = $sid;
            }
            $this->pdo->commit();
        } catch (PDOException $e) {
            $this->pdo->rollBack();
            throw $e;
        }

        return $map;
    }

    /**
     * (Optional convenience) Same as above, but accepts codes and resolves IDs internally.
     */
    public function ensureIdsForMasterTextsByCodes(
        string $clientCode,
        string $type,
        string $resourceSubject,
        string $resourceVariant,
        array $masters
    ): array {
        // resolve clientId
        $st = $this->pdo->prepare('SELECT clientId FROM i18n_clients WHERE clientCode=? LIMIT 1');
        $st->execute([$clientCode]);
        $cid = $st->fetchColumn();
        if ($cid === false) {
            throw new \RuntimeException("Unknown clientCode '{$clientCode}'");
        }

        // resolve resourceId
        $st = $this->pdo->prepare(
            'SELECT resourceId FROM i18n_resources WHERE `type`=? AND subject=? AND variant=? LIMIT 1'
        );
        $st->execute([$type, $resourceSubject, $resourceVariant]);
        $rid = $st->fetchColumn();
        if ($rid === false) {
            throw new \RuntimeException("Unknown resource {$type}/{$resourceSubject}/{$resourceVariant}");
        }

        return $this->ensureIdsForMasterTexts((int)$cid, (int)$rid, $masters);
    }
}
