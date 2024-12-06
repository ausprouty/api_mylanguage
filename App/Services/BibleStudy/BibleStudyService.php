<?php
namespace App\Services\BibleStudy;

use App\Renderers\RendererFactory;
use \InvalidArgumentException;
use App\Traits\BibleStudyFileNamingTrait;
use App\Repositories\LanguageRepository;
use App\Services\ResourceStorageService;

class BibleStudyService {

    use BibleStudyFileNamingTrait;

    private $rendererFactory;
    private $languageRepository;

    public function __construct(
        RendererFactory $rendererFactory, 
        LanguageRepository $languageRepository,
    ) {
            
        $this->rendererFactory = $rendererFactory;
        $this->languageRepository = $languageRepository;
    }
        
    public function fetchStudy(
        string $study, string $format, string $session, string $languageCodeHL1, string $languageCodeHL2 = null): string {
        $filename = $this->getFileName($study, $format, $session, $languageCodeHL1, $languageCodeHL2);
        $storagePath = $this->getStoragePath($study, $format);
        $storageService = new ResourceStorageService($storagePath);
        $file = $storageService->retrieve($filename);
        if ($file){
            return $file;
        }
        else{
            return "not found";
        }
        
        /* Example: Generate content
        $content = $this->generateStudyContent($type);

        // Get the appropriate renderer and render the content
        $renderer = $this->rendererFactory->getRenderer($format);
        return $renderer->render($content);
        */
        return '';
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
