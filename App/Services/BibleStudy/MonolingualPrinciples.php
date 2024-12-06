<?php
namespace App\Services\BibleStudy;
use App\Services\BibleStudy\AbstractMonolingualStudy;

class MonoLingualPrinciples extends AbstractMonoLingualStudy {
    protected $studyType = 'principles';

    public function getContent(): array {
        // Query database for Principles study content
        return $this->db->fetchContent('principles', $this->language, $this->format);
    }
}
