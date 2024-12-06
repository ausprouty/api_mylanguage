<?php
namespace App\Services\BibleStudy;
use App\Services\BibleStudy\AbstractMonolingualStudy;

class MonoLingualLeadership extends AbstractMonoLingualStudy {
    protected $studyType = 'leadership';

    public function getContent(): array {
        // Query database for Leadership study content
        return $this->db->fetchContent('leadership', $this->language, $this->format);
    }
}
