<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class I18nClientsRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Look up i18n_clients.clientId by clientCode (e.g., 'wsu').
     * Returns null if not found.
     */
    public function getIdByCode(string $clientCode): ?int
    {
        $sql = 'SELECT clientId
                  FROM i18n_clients
                 WHERE clientCode = ?
                 LIMIT 1';
        $st = $this->pdo->prepare($sql);
        $st->execute([$clientCode]);
        $id = $st->fetchColumn();
        return $id !== false ? (int)$id : null;
    }
}
