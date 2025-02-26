<?php

namespace App\Controllers\BibleStudy;

use App\Services\BibleStudy\TitleService;

class StudyTitleController
{
    protected $titleService;

    public function __construct(TitleService $titleService)
    {
        $this->titleService = $titleService;
    }

    public function webGetTitleForStudy(ServerRequestInterface $request): ResponseInterface
    {
        $params = $request->getAttribute('routeInfo')[2];
        $study = $params['study'];
        $languageCodeHL = $params['languageCodeHL'];

        $titleData = $this->titleService->getTitleAndLessonNumber($study, $languageCodeHL);

        return new \Laminas\Diactoros\Response\JsonResponse([
            'title' => $titleData['title'],
            'lessonNumber' => $titleData['lessonNumber']
        ]);
    }
}
