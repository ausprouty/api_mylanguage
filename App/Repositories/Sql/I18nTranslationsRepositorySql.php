<?php
declare(strict_types=1);

namespace App\Repositories\Sql;

use App\Repositories\I18nTranslationsRepository;
use App\Services\Database\DatabaseService;

final class I18nTranslationsRepositorySql implements I18nTranslationsRepository
{
    public function __construct(private DatabaseService $db) {}

    public function fetchForLanguage(
        array $stringIds,
        string $language,
        ?string $client,
        ?string $variant
    ): array {
        if (empty($stringIds)) return [];

        // Example strategy: prefer (client,variant), then (client,null),
        // then (null,variant), then (null,null). Use ORDER BY priority and DISTINCT.
        $in = implode(',', array_fill(0, count($stringIds), '?'));

        $sql = "
          SELECT DISTINCT t.string_id AS stringId, t.text
          FROM i18n_translations t
          WHERE t.string_id IN ($in)
            AND t.languageCodeHL = ?
            AND ( (t.client IS NULL AND ? IS NULL) OR t.client = ? )
            AND ( (t.variant IS NULL AND ? IS NULL) OR t.variant = ? )
        ";
        // If you prefer explicit fallback queries, run multiple selects in order and merge.

        $params = array_merge($stringIds, [$language, $client, $client, $variant, $variant]);
        $rows = $this->db->fetchAll($sql, $params);

        // Ensure camelCase keys
        return array_map(
            fn($r) => ['stringId' => (int)$r['stringId'], 'text' => (string)$r['text']],
            $rows
        );
    }
}
