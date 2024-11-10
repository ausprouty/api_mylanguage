<?php

namespace App\Controllers\BibleStudy\Monolingual;

use App\Controllers\BibleStudy\DbsStudyController;
use App\Models\BibleStudy\DbsReferenceModel;
use App\Services\QrCodeGeneratorService;
use App\Traits\MonolingualFileNamingTrait;
use App\Traits\MonolingualTemplatePathsTrait;
use App\Traits\MonolingualQrCodeTrait;

class MonolingualDbsTemplateController extends MonolingualStudyTemplateController
{
    use MonolingualFileNamingTrait, MonolingualTemplatePathsTrait, MonolingualQrCodeTrait;

    private QrCodeGeneratorService $qrCodeGeneratorService;

    public function __construct(
        QrCodeGeneratorService $qrCodeGeneratorService, 
        string $lesson, 
        string $languageCodeHL
    ) {
        $this->qrCodeGeneratorService = $qrCodeGeneratorService;
        parent::__construct($lesson, $languageCodeHL);
    }

    protected function findTitle(string $lesson, string $languageCodeHL): string {
        return DbsStudyController::getTitle($lesson, $languageCodeHL);
    }

    protected function getMonolingualPdfTemplateName(): string {
        return 'monolingualDbsPdf.template.html';
    }

    protected function getMonolingualViewTemplateName(): string {
        return 'monolingualDbsView.template.html';
    }

    protected function getStudyReferenceInfo(string $lesson): DbsReferenceModel {
        $studyReferenceInfo = new DbsReferenceModel();
        $studyReferenceInfo->setLesson($lesson);
        return $studyReferenceInfo;
    }

    protected function getTranslationSource(): string {
        return 'dbs';
    }

    protected function setFileName(): void {
        $this->fileName = 'DBS' . $this->lesson . '(' . $this->language1->getName() . ')';
        $this->fileName = str_replace(' ', '_', $this->fileName);
    }

    protected function setUniqueTemplateValues(): void {
        // Add any DBS-specific values here if needed
    }

    protected function getFileNamePrefix(): string {
        return 'DBS';
    }

    protected static function getPathPrefix(): string {
        return 'dbs';
    }

    // Override createQrCode to use the injected QrCodeGeneratorService
    protected function createQrCode(string $url, string $languageCodeHL): string {
        $fileName = $this->getFileNamePrefix() . $this->lesson . '-' . $languageCodeHL . '.png';
        
        $this->qrCodeGeneratorService->initialize($url, 240, $fileName);
        $this->qrCodeGeneratorService->generateQrCode();

        return $this->qrCodeGeneratorService->getQrCodeUrl();
    }
}
