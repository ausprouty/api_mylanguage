<?php

namespace App\Cron;

use App\Services\Database\DatabaseService;
use App\Services\Language\TranslationBatchService;
use App\Services\LoggerService;
use App\Services\ThrottleService;
use App\Configuration\Config;

class TranslationQueueProcessor
{
    protected DatabaseService $db;
    protected TranslationBatchService $translator;

    public function __construct()
    {
        $this->db = new DatabaseService('standard');
        $this->translator = new TranslationBatchService();
    }

    public function runIfAuthorized(?string $receivedToken = null): void
    {
        LoggerService::logInfo('TranslationQueueProcessor-24',$receivedToken);
   
        $throttle = new ThrottleService('translation_queue');
        if ($throttle->tooSoon(200)) {
            LoggerService::logInfo('TranslationQueueProcessor-28', 'Too Soon');
            echo json_encode(['status' => 'skip - too soon']);
            return;
        }
        LoggerService::logInfo('TranslationQueueProcessor-32', 'Time is ready');       
        
        $authorized = $this->checkCronKey($receivedToken);
        if (!$authorized){
             LoggerService::logWarning('TranslationQueueProcessor-36', 'Not Authorized');
             echo json_encode(['status' => 'not authorized']);
            return;
        }
        LoggerService::logInfo('TranslationQueueProcessor-40', 'Authorized');       
        
        $this->run();

        $throttle->updateTimestamp();
        echo json_encode(['status' => 'processed']);
    }

    public function run(): void
    {
        LoggerService::logInfo('TranslationQueueProcessor', 'Starting run');

        $items = $this->db->fetchAll(
            "SELECT * FROM translation_queue ORDER BY id ASC LIMIT 100"
        );

        if (empty($items)) {
            LoggerService::logInfo('TranslationQueueProcessor', "No items to process.");
            return;
        }

        $grouped = $this->groupByLanguage($items);

        foreach ($grouped as $langCode => $texts) {
            $filteredTexts = array_filter($texts, function ($text) {
                $text = trim($text);
                if ($text === '') return false;
                if (preg_match('/^\d{4}-\d{2}-\d{2}T\d{2}:\d{2}:\d{2}(?:\+\d{2}:\d{2}|Z)?$/', $text)) return false;
                if (is_numeric($text)) return false;
                return true;
            });

            $skipped = array_diff($texts, $filteredTexts);
            if (!empty($skipped)) {
                file_put_contents(
                    __DIR__ . '/skipped_translations.log',
                    print_r($skipped, true),
                    FILE_APPEND
                );
            }

            if (empty($filteredTexts)) {
                LoggerService::logInfo('TranslationQueueProcessor', "No valid texts for $langCode.");
                continue;
            }

            $filteredValues = array_values($filteredTexts);
            $translations = $this->translator->translateBatch($filteredValues, $langCode);

            foreach ($translations as $i => $translatedText) {
                $originalText = $filteredValues[$i];

                if (empty(trim($translatedText)) || $translatedText === $originalText) {
                    continue;
                }

                $this->storeTranslation($langCode, $originalText, $translatedText);
            }
        }

        $this->removeQueueItems(array_column($items, 'id'));
        LoggerService::logInfo('TranslationQueueProcessor', "Processed " . count($items) . " items.");
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
         LoggerService::logInfo('TranslationQueueProcessor-114', "storing $langCode   $original");
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

    protected function checkCronKey($cronKey){
        $result = $this->db->fetchSingleValue("SELECT id FROM cron_tokens 
        WHERE token = :token",  [':token' => $cronKey]);
        if (!$result){
            return false;
        }
         // Delete key to make it one-time use
        $this->db->executeQuery("DELETE FROM cron_tokens 
            WHERE token = :token", [':token' => $cronKey]);
        return true;

    }
}
