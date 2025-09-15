<?php
declare(strict_types=1);

namespace App\Repositories\Sql;

use App\Repositories\I18nStringsRepository;
use App\Services\Database\DatabaseService;

final class I18nStringsRepositorySql implements I18nStringsRepository
{
    public function __construct(private DatabaseService $db) {}

    public function ensureIdsForMasterTexts(
        string $kind,
        string $subject,
        array $masterTexts
    ): array {
        // 1) Insert-miss texts (use INSERT IGNORE / ON DUP KEY) to get stable ids
        $sql = "INSERT INTO i18n_strings (kind, subject, text)
                VALUES (:kind, :subject, :text)
                ON DUPLICATE KEY UPDATE text = text";
        $stmt = $this->db->prepare($sql);
        foreach (array_unique($masterTexts) as $txt) {
            $stmt->execute([':kind'=>$kind, ':subject'=>$subject, ':text'=>$txt]);
        }

        // 2) Read back ids; alias to camelCase
        $sel = "SELECT id AS stringId, text
                FROM i18n_strings
                WHERE kind = :kind AND subject = :subject AND text IN (" .
                implode(',', array_fill(0, count($masterTexts), '?')) . ")";
        $params = array_merge([$kind, $subject], $masterTexts);
        // If your DB layer wants named params, adapt accordingly.

        $rows = $this->db->fetchAll($sel, $params);

        $out = [];
        foreach ($rows as $r) {
            $out[$r['text']] = ['id' => (int)$r['stringId']];
        }
        return $out;
    }
}
