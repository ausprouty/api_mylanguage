<?php
namespace App\Services\Language;

use App\Services\Database\DatabaseService;

class TranslationMemoryService
{
    private $db;

    public function __construct(DatabaseService $db)
    {
        $this->db = $db;
    }

    public function get(string $source, string $targetLang): ?string
    {
        $query = "SELECT translated_text FROM translation_memory
                  WHERE source_text = :source AND target_lang = :target";
        return $this->db->fetchColumn($query, [
            ':source' => $source,
            ':target' => $targetLang
        ]);
    }

    public function save(string $source, string $targetLang, string $translated): void
    {
        $query = "INSERT INTO translation_memory (source_text, target_lang, translated_text)
                  VALUES (:source, :target, :translated)
                  ON DUPLICATE KEY UPDATE translated_text = :translated";
        $this->db->executeQuery($query, [
            ':source'     => $source,
            ':target'     => $targetLang,
            ':translated' => $translated
        ]);
    }
}
