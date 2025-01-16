<?php

namespace App\Services\BibleStudy;

use Exception;
use App\Services\LoggerService;
use App\Configuration\Config;

class TemplateService
{
    
    public function getStudyTemplateName($format, $study, $render): string {
        $name = '';
    
        // Determine the format type
        if ($format == 'monolingual') {
            $name .= 'monolingual';
        } else {
            $name .= 'bilingual';
        }

        $name .= ucfirst($study) .'Study';
    
        // Capitalize the first letter of $render and append it
        $name .= ucfirst($render) . '.twig';
        return $name;
    }
    
    
    public function getTemplate(string $template): string
    {
        try {
            // Initialize the logger
            LoggerService::init();

            // Get the directory path
            $dir = Config::getDir('resources.templates');
            if (!$dir) {
                $message = "Templates directory not configured.";
                LoggerService::logError($message);
                throw new \RuntimeException($message);
            }

            // Ensure the directory path ends with a slash
            $dir = rtrim($dir, DIRECTORY_SEPARATOR) . DIRECTORY_SEPARATOR;

            // Sanitize the template name to prevent directory traversal attacks
            $sanitizedTemplate = basename($template);

            // Restrict to .twig files only
            if (pathinfo($sanitizedTemplate, PATHINFO_EXTENSION) !== 'twig') {
                $message = "Invalid file type requested: $sanitizedTemplate.";
                LoggerService::logError($message);
                throw new \RuntimeException($message);
            }

            // Construct the full path
            $templatePath = $dir . $sanitizedTemplate;

            // Check if the file exists
            if (!file_exists($templatePath)) {
                $message = "Template file not found: $templatePath.";
                LoggerService::logError($message);
                throw new \RuntimeException($message);
            }

            // Read the file contents
            $file = file_get_contents($templatePath);
            if ($file === false) {
                $message = "Failed to read template file: $templatePath.";
                LoggerService::logError($message);
                throw new \RuntimeException($message);
            }

            LoggerService::logInfo("Template file successfully retrieved: $templatePath");
            return $file;
        } catch (\Exception $e) {
            // Log the exception
            LoggerService::logError($e->getMessage());
            // Optionally rethrow the exception
            throw $e;
        }
    }
}
