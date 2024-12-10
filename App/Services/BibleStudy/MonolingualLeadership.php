<?php

namespace App\Services\BibleStudy;

use App\Services\BibleStudy\AbstractMonolingualStudy;
use App\Configuration\Config;

class MonoLingualLeadership extends AbstractMonoLingualStudy
{
    protected $studyType = 'leadership';

    public function getTemplate(string $format): string
    {
        // Determine the template file based on the format
        if ($format === 'view') {
            $template = $this->templateService('monolingualLeadershipView.twig');
        } elseif ($format === 'pdf') {
            $template = $this->templateService('monolingualLeadershipView.twig');
        } else {
            $message = "Invalid format specified: $format.";
            LoggerService::logError($message);
            throw new \InvalidArgumentException($message);
        }
    }
}
