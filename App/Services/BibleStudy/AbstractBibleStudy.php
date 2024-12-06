<?php

namespace App\Services\BibleStudy;

abstract class AbstractBibleStudy {
    protected $studyType;
    protected $format;
    protected $language;
    protected $db;

    public function __construct($db) {
        $this->db = $db;
    }

    abstract public function getContent(): array; // To be implemented by child classes

    public function getMetadata(): array {
        return [
            'studyType' => $this->studyType,
            'format' => $this->format,
            'language' => $this->language,
        ];
    }
}
