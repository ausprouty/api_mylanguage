<?php

namespace App\Services\BibleStudy;

use App\Services\BibleStudy\AbstractMonolingualStudy;
use App\Configuration\Config;
use App\Services\LoggerService;

class MonoLingualPrinciples extends AbstractMonoLingualStudy
{
    protected $studyType = 'principles';

    public function getTemplate(string $format): string
    {
        // Determine the template file based on the format
        if ($format === 'view') {
            $template = $this->templateService->getTemplate('monolingualLifePrinciplesView.twig');
        } elseif ($format === 'pdf') {
            $template = $this->templateService->getTemplate('monolingualLifePrinciplesView.twig');
        } else {
            $message = "Invalid format specified: $format.";
            LoggerService::logError($message);
            throw new \InvalidArgumentException($message);
        }
        return $template;
    }
}
