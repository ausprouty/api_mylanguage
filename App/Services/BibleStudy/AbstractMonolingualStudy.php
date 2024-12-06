<?php
namespace App\Services\BibleStudy;
abstract class AbstractMonoLingualStudy extends AbstractBibleStudy {
    public function __construct($db, $language) {
        parent::__construct($db);
        $this->language = $language;
    }
}
