<?php
use App\Controllers\BibleStudyController;
use App\Services\StudyService;
use App\Renderers\RendererFactory;
use App\Renderers\HtmlRenderer;
use App\Renderers\PdfRenderer;
use App\Repositories\LanguageRepository;

use function DI\create;
use function DI\get;

return [
    RendererFactory::class => create()
        ->constructor([
            'html' => get(HtmlRenderer::class),
            'pdf' => get(PdfRenderer::class),
        ]),

    StudyService::class => create()
        ->constructor(
            get(RendererFactory::class),
            get(LanguageRepository::class)
        ),

    BibleStudyController::class => create()
        ->constructor(get(StudyService::class)),

    HtmlRenderer::class => create(),
    PdfRenderer::class => create(),
    LanguageRepository::class => create(), // Adjust if LanguageRepository has dependencies
];
