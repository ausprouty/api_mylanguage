<?php
declare(strict_types=1);

namespace App\Repositories;

use PDO;

final class I18nTranslationsRepository
{
    public function __construct(private PDO $pdo) {}

    /**
     * Fetch translations for the given stringIds in a target HL code (e.g., 'frn00').
     * Returns rows like: ['stringId' => int, 'translatedText' => string, 'status' => string]
     */
    public function fetchByStringIdsAndLanguage(
        array $stringIds,
        string $languageCodeHL
    ): array {
        if (empty($stringIds)) {
            return [];
        }

        $placeholders = implode(',', array_fill(0, count($stringIds), '?'));

        $sql = "SELECT stringId, translatedText, status
                  FROM i18n_translations
                 WHERE languageCodeHL = ?
                   AND stringId IN ($placeholders)";

        $stmt = $this->pdo->prepare($sql);
        $params = array_merge([$languageCodeHL], array_values($stringIds));
        $stmt->execute($params);

        $rows = [];
        while ($r = $stmt->fetch(PDO::FETCH_ASSOC)) {
            $rows[] = [
                'stringId'       => (int)$r['stringId'],
                'translatedText' => (string)$r['translatedText'],
                'status'         => isset($r['status']) ? (string)$r['status'] : 'draft',
            ];
        }
        return $rows;
    }
}
