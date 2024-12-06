<?php
namespace App\Services\BibleStudy;

use App\Renderers\RendererFactory;
use \InvalidArgumentException;
use App\Traits\BibleStudyFileNamingTrait;
use App\Repositories\LanguageRepository;

class BibleStudyService {

    use BibleStudyFileNamingTrait;

    private $rendererFactory;
    private $languageRepository;

    public function __construct(
        RendererFactory $rendererFactory, 
        LanguageRepository $languageRepository) {
            
        $this->rendererFactory = $rendererFactory;
        $this->languageRepository = $languageRepository;
    }
        
    public function fetchStudy(
        string $study, string $format, string $session, string $languageCodeHL1, string $languageCodeHL2 = null): string {
        $filename = $this->generateFileName($study, $format, $session, $languageCodeHL1, $languageCodeHL2);
        print_r ('I came to fetch '. $filename);
        
        /* Example: Generate content
        $content = $this->generateStudyContent($type);

        // Get the appropriate renderer and render the content
        $renderer = $this->rendererFactory->getRenderer($format);
        return $renderer->render($content);
        */
        return 'frodo;
    }

    private function generateStudyContent(string $type): string {
        return match ($type) {
            'dbs' => "Content for DBS study",
            'leadership' => "Content for Leadership study",
            'life' => "Content for Life study",
            default => throw new InvalidArgumentException("Unknown study type: $type"),
        };
    }
}
