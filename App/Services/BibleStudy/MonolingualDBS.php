<?php

namespace App\Services\BibleStudy;

use App\Services\BibleStudy\AbstractMonolingualStudy;


class MonoLingualDBS extends AbstractMonoLingualStudy
{
    protected $studyType = 'dbs';

    public function getStudyInfo(): array
    {
        $info = [];
        return $info;
    }

    
}
