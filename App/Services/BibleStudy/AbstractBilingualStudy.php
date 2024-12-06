<?php
namespace App\Services\BibleStudy;
abstract class AbstractBiLingualStudy extends AbstractBibleStudy {
   
    protected $secondaryLanguage;

    public function __construct($db, $primaryLanguage, $secondaryLanguage) {
        parent::__construct($db);
        $this->language = $primaryLanguage;
        $this->secondaryLanguage = $secondaryLanguage;
    }

    public function getSecondaryLanguage(): string {
        return $this->secondaryLanguage;
    }
}
