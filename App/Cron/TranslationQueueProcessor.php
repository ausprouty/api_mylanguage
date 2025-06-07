<?php

namespace App\Cron;

use App\Services\Database\DatabaseService;
use App\Services\Language\TranslationBatchService;

class TranslationQueueProcessor
{
    protected DatabaseService $db;
    protected TranslationBatchService $translator;

    public function __construct()
    {
        $this->db = new DatabaseService('standard');
        $this->translator = new TranslationBatchService();
    }

    public function run(): void
    {
        $items = $this->db->fetchAll(
            "SELECT * FROM translation_queue ORDER BY id ASC LIMIT 100"
        );

        if (empty($items)) {
            echo "No items to process.\n";
            return;
        }

        $grouped = $this->groupByLanguage($items);

        foreach ($grouped as $langCode => $texts) {
            $translations = $this->translator->translateBatch($texts, $langCode);

            foreach ($translations as $i => $translatedText) {
                $originalText = $texts[$i];
                $this->storeTranslation($langCode, $originalText, $translatedText);
            }
        }

        $this->removeQueueItems(array_column($items, 'id'));

        echo "Processed " . count($items) . " items.\n";

        $this->db->closeConnection();
    }

    protected function groupByLanguage(array $items): array
    {
        $grouped = [];
        foreach ($items as $item) {
            $grouped[$item['target_lang']][] = $item['source_text'];
        }
        return $grouped;
    }

    protected function storeTranslation(string $langCode, string $original, string $translated): void
    {
        $this->db->executeQuery(
            "INSERT IGNORE INTO translation_memory 
            (source_text, source_lang, target_lang, translated_text)
            VALUES (:source_text, 'en', :target_lang, :translated_text)",
            [
                ':source_text' => $original,
                ':target_lang' => $langCode,
                ':translated_text' => $translated
            ]
        );
    }

    protected function removeQueueItems(array $ids): void
    {
        if (empty($ids)) return;
        $placeholders = implode(',', array_fill(0, count($ids), '?'));
        $this->db->executeQuery(
            "DELETE FROM translation_queue WHERE id IN ($placeholders)",
            $ids
        );
    }
}
