<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class I18nResourcesRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Look up i18n_resources.resourceId by (type, subject, variant).
     * Returns null if no row exists.
     */
    public function getIdByTypeSubjectVariant(
        string $type,
        string $subject,
        string $variant
    ): ?int {
        // backtick `type` because it can be treated specially by some SQL parsers
        $sql = 'SELECT resourceId
                  FROM i18n_resources
                 WHERE `type` = ?
                   AND subject = ?
                   AND variant = ?
                 LIMIT 1';
        $st = $this->pdo->prepare($sql);
        $st->execute([$type, $subject, $variant]);
        $id = $st->fetchColumn();
        return $id !== false ? (int)$id : null;
    }
}
