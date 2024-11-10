<?php

namespace App\Controllers\BibleStudy\Monolingual;

use App\Controllers\BiblePassage\PassageSelectController;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;
use App\Models\Language\TranslationModel;
use App\Models\Language\LanguageModel;
use App\Repositories\BibleRepository;
use App\Repositories\LanguageRepository;

abstract class MonolingualStudyTemplateController
{
    protected BibleModel $bible1;
    protected string $bibleBlock = '';
    protected ?PassageSelectController $biblePassage1 = null;
    protected BibleReferenceInfoModel $bibleReferenceInfo;
    protected string $testament;
    protected string $fileName;
    protected LanguageModel $language1;
    protected string $lesson;
    protected string $qrcode1;
    protected ?string $template = null;
    protected mixed $studyReferenceInfo;
    protected string $title;
    protected array $translation1 = [];

    abstract protected function createQRCode(string $url, string $languageCodeHL): string;
    abstract static function findFileName(string $lesson, string $languageCodeHL1): string;
    abstract static function findFileNamePdf(string $lesson, string $languageCodeHL1): string;
    abstract static function findFileNameView(string $lesson, string $languageCodeHL1): string;
    abstract protected function findTitle(string $lesson, string $languageCodeHL1): string;
    abstract protected function getMonolingualPdfTemplateName(): string;
    abstract protected function getMonolingualViewTemplateName(): string;
    abstract static function getPathPdf(): string;
    abstract static function getUrlPdf(): string;
    abstract static function getPathView(): string;
    abstract protected function getStudyReferenceInfo(string $lesson): mixed;
    abstract protected function getTranslationSource(): string;
    abstract protected function setFileName(): void;
    abstract protected function setUniqueTemplateValues(): void;

    public function __construct(LanguageRepository $languageRepository, string $lesson, string $languageCodeHL1) {   
        $this->language1 = new LanguageModel($languageRepository);
        $this->language1->findOneByLanguageCodeHL($languageCodeHL1);
        
        $this->lesson = $lesson;
        $this->fileName = $this->findFileName($lesson, $languageCodeHL1);
        $this->title = $this->findTitle($lesson, $languageCodeHL1);
        
        $this->setTranslation($this->getTranslationSource());
        $this->studyReferenceInfo = $this->getStudyReferenceInfo($lesson);
        
        $this->bibleReferenceInfo = new BibleReferenceInfoModel();
        $this->bibleReferenceInfo->setFromEntry($this->studyReferenceInfo->getEntry());
        $this->testament = $this->bibleReferenceInfo->getTestament();
        
        $this->bible1 = $this->findBibleOne($languageCodeHL1, $this->testament);
        $this->setPassage();
        
        $this->qrcode1 = $this->createQRCode($this->biblePassage1->getPassageUrl(), $languageCodeHL1);
    }

    protected function setTranslation(string $source = 'dbs'): void {
        $translationModel = new TranslationModel($this->language1->getLanguageCodeHL(), $source);
        $this->translation1 = $translationModel->getTranslationFile();
    }

    public function getFileName(): string {
        return $this->fileName;
    }

    public function getTranslation1(): array {
        return $this->translation1;
    }

    public function getTemplate(): ?string {
        return $this->template;
    }

    protected function findBibleOne(string $languageCodeHL1, string $testament = 'NT'): BibleModel {
        $bible = new BibleModel(new BibleRepository());
        $bible->setBestDbsBibleByLanguageCodeHL($languageCodeHL1, $testament);
        
        return $bible;
    }

    public function setPassage(): void {
        $this->biblePassage1 = new PassageSelectController($this->bibleReferenceInfo, $this->bible1);
    }

    private function replacePlaceholder(string $placeholder, string $value): void {
        $this->template = str_replace($placeholder, $value, $this->template);
    }

    private function replacePlaceholderWithSpan(string $placeholder, string $value): void {
        $span = '<span dir="{{dir_language1}}" style="font-family:{{font_language1}};">' . $value . '</span>';
        $this->template = str_replace($placeholder, $span, $this->template);
    }

    public function setMonolingualTemplate(string $template = 'monolingualDbs.template.html'): void {
        $filePath = ROOT_TEMPLATES . $template;
        if (!file_exists($filePath)) {
            writeLogError('MonolingualStudyTemplateController-75', 'Template file not found: ' . $filePath);
            return;
        }

        $this->template = file_get_contents($filePath);
        $this->createBibleBlock();

        $this->replacePlaceholderWithSpan('{{Bible Block}}', $this->bibleBlock);
        $this->replacePlaceholderWithSpan('{{language}}', $this->language1->getName());
        $this->replacePlaceholder('{{Bible Reference}}', $this->biblePassage1->getReferenceLocalLanguage());
        $this->replacePlaceholder('{{url}}', $this->biblePassage1->getPassageUrl());
        $this->replacePlaceholder('{{QrCode1}}', $this->qrcode1);
        $this->replacePlaceholderWithSpan('{{Title}}', $this->title);
        $this->replacePlaceholderWithSpan('{{filename}}', $this->getFileName());
        $this->replacePlaceholderWithSpan('{{Video Block}}', '');

        foreach ($this->translation1 as $key => $value) {
            $this->replacePlaceholderWithSpan('{{' . $key . '}}', $value);
        }
        
        $this->replacePlaceholder('{{dir_language1}}', $this->language1->getDirection());
        $this->replacePlaceholder('{{font_language1}}', $this->language1->getFont());
        $this->setUniqueTemplateValues();
    }

    private function createBibleBlock(): void {
        if ($this->biblePassage1 && $this->biblePassage1->getPassageText() !== null) {
            $this->bibleBlock = $this->biblePassage1->getPassageText();
        } else {
            $this->createBibleBlockWhenTextMissing();
        }
    }

    private function createBibleBlockWhenTextMissing(): void {
        $this->bibleBlock = $this->showTextOrLink($this->biblePassage1);
    }

    private function showTextOrLink(PassageSelectController $biblePassage): string {
        return $biblePassage->getPassageText() === null ? $this->showDivLink($biblePassage) : $this->showDivText($biblePassage);
    }

    private function showDivLink(PassageSelectController $biblePassage): string {
        $template = $this->loadTemplate('bibleBlockDivLink.template.html');
        return str_replace(
            ['{{dir_language}}', '{{url}}', '{{Bible Reference}}', '{{Bid}}'],
            [$biblePassage->getBibleDirection(), $biblePassage->getPassageUrl(), $biblePassage->getReferenceLocalLanguage(), $biblePassage->getBibleBid()],
            $template
        );
    }

    private function showDivText(PassageSelectController $biblePassage): string {
        $template = $this->loadTemplate('bibleBlockDivText.template.html');
        return str_replace(
            ['{{dir_language}}', '{{url}}', '{{Bible Reference}}', '{{Bid}}', '{{passage_text}}'],
            [$biblePassage->getBibleDirection(), $biblePassage->getPassageUrl(), $biblePassage->getReferenceLocalLanguage(), $biblePassage->getBibleBid(), $biblePassage->getPassageText()],
            $template
        );
    }

    private function loadTemplate(string $fileName): string {
        $filePath = ROOT_TEMPLATES . $fileName;
        if (!file_exists($filePath)) {
            throw new \Exception('Template file not found: ' . $filePath);
        }
        return file_get_contents($filePath);
    }

    public function saveMonolingualView(): void {
        $filePath = $this->getPathView() . $this->fileName . '.html';
        file_put_contents($filePath, $this->template);
    }
}
