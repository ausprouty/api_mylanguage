<?php

namespace App\Services;

use Twig\Environment;
use Twig\Loader\FilesystemLoader;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Error\SyntaxError;
use App\Configuration\Config;
use App\Services\Debugging;
use RuntimeException;

class TwigService
{
    private Environment $twig;

    public function __construct()
    {
        $templateDirectory = Config::getDir('resources.templates');
        $loader = new FilesystemLoader($templateDirectory);
        $this->twig = new Environment($loader, [
            'cache' => Config::getDir('twig_cache'), // Optional: enable caching
            'debug' => true,             // Optional: enable debugging
        ]);
        $this->twig->addExtension(new \Twig\Extension\DebugExtension());
    }

    /**
     * Renders a Twig template with the given data.
     *
     * @param string $template The template file name (e.g., 'template.twig').
     * @param array $data Associative array of data for substitutions.
     * @return string Rendered template content.
     * @throws LoaderError|RuntimeError|SyntaxError
     */
    public function render(string $template, array $data = []): string
    {
        return $this->twig->render($template, $data);
    }

    public function renderFromString(string $templateContent, array $data = []): string
    {
        try {
            $template = $this->twig->createTemplate($templateContent);
            return $template->render($data);
        } catch (\Twig\Error\Error $e) {
            // Handle errors (e.g., log or rethrow)
            throw new RuntimeException("Error rendering template: " . $e->getMessage(), 0, $e);
        }
    }
    public function buildMonolingualTwig(
        $studyTemplateName, 
        $bibleTemplateName,
        $videoTemplateName,
        $translation
        ){
        print_r($translation['language1']);
        return $this->twig->render('mainStudy.twig', [
            'study_template' => $studyTemplateName, 
            'bible_block' => $bibleTemplateName,
            'video_block' => $videoTemplateName,
            'translation' => $translation['language1'],
            'dir_language1'=> 'ltr'
        ]);
    }

}
