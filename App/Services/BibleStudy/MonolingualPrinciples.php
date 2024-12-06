<?php
namespace App\Services\BibleStudy;
use App\Services\BibleStudy\AbstractMonolingualStudy;

class MonoLingualPrinciples extends AbstractMonoLingualStudy {
    protected $studyType = 'principles';

   

    public function getStudyInfo():array {
        $info = [];
        return $info;
    }

    
    public function getLanguageInfo(): void{
        return;
      }
}
