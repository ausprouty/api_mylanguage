<?php
namespace App\Services\BibleStudy;
use App\Services\BibleStudy\AbstractMonolingualStudy;

class MonoLingualLeadership extends AbstractMonoLingualStudy {
    protected $studyType = 'leadership';

    public function getStudyInfo():array {
        $info = [];
        return $info;
    }

   
    public function getLanguageInfo(): void{
        return;
      }
}
