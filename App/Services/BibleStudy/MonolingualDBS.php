<?php
namespace App\Services\BibleStudy;
use App\Services\BibleStudy\AbstractMonolingualStudy;


class MonoLingualDBS extends AbstractMonoLingualStudy {
    protected $studyType = 'dbs';

    public function getContent(): array {
        // Query database for DBS study content in the primary language
        return $this->db->fetchContent('dbs', $this->language, $this->format);
    }
}
