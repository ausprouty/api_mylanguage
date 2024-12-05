<?php
namespace App\Services;

use App\Renderers\RendererFactory;

class StudyService {
    private $rendererFactory;

    public function __construct(RendererFactory $rendererFactory) {
        $this->rendererFactory = $rendererFactory;
    }
        
    public function fetchStudy(string $study, string $session, string $format, string $language1, string $language2 = null): string {
        print_r ('I came to fetch');
        
        /* Example: Generate content
        $content = $this->generateStudyContent($type);

        // Get the appropriate renderer and render the content
        $renderer = $this->rendererFactory->getRenderer($format);
        return $renderer->render($content);
        */
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
