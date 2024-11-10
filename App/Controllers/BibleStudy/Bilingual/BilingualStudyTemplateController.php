<?php
namespace App\Controllers\BibleStudy\Bilingual;

use App\Controllers\BibleStudy\BibleBlockController;
use App\Controllers\BiblePassage\PassageSelectController;
use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceInfoModel;
use App\Models\Language\TranslationModel;
use App\Models\Language\LanguageModel;
use App\Repositories\LanguageRepository;
use App\Repositories\BibleRepository;

abstract class BilingualStudyTemplateController
{
    protected LanguageRepository $languageRepository;
    protected BibleRepository $bibleRepository;
    protected BibleModel $bible1;
    protected BibleModel $bible2;
    protected string $bibleBlock;
    protected PassageSelectController $biblePassage1;
    protected PassageSelectController $biblePassage2;
    protected BibleReferenceInfoModel $bibleReferenceInfo;
    protected string $testament;
    protected string $fileName;
    protected LanguageModel $language1;
    protected LanguageModel $language2;
    protected string $lesson;
    protected string $qrcode1;
    protected string $qrcode2;
    protected string $template;
    protected string $studyReferenceInfo;
    protected string $title;
    protected string $translation1;
    protected string $translation2;

    // Define frequently used placeholders as constants
    protected const PLACEHOLDERS = [
        'BIBLE_BLOCK' => '{{Bible Block}}',
        'LANGUAGE' => '{{language}}',
        'BIBLE_REFERENCE' => '{{Bible Reference}}',
        'URL' => '{{url}}',
        'QRCODE1' => '{{QrCode1}}',
        'TITLE' => '{{Title}}',
        'FILENAME' => '{{filename}}',
        'VIDEO_BLOCK' => '{{Video Block}}',
    ];

    abstract protected function createQRCode($url, $languageCodeHL);
    abstract static function findFileName($lesson, $languageCodeHL1, $languageCodeHL2);
    abstract static function findFileNamePdf($lesson, $languageCodeHL1, $languageCodeHL2);
    abstract protected function findTitle($lesson, $languageCodeHL1);
    abstract protected function getBilingualTemplateName();
    abstract static function getPathPdf();
    abstract static function getUrlPdf();
    abstract static function getPathView();
    abstract protected function getStudyReferenceInfo($lesson);
    abstract protected function getTranslationSource();
    abstract protected function setFileName();
    abstract protected function setUniqueTemplateValues();

    public function __construct(LanguageRepository $languageRepository, BibleRepository $bibleRepository, string $languageCodeHL1, string $languageCodeHL2, $lesson)
    {
        $this->languageRepository = $languageRepository;
        $this->bibleRepository = $bibleRepository;
        
        $this->language1 = new LanguageModel($languageRepository);
        $this->language1->findOneByLanguageCodeHL($languageCodeHL1);
        $this->language2 = new LanguageModel($languageRepository);
        $this->language2->findOneByLanguageCodeHL($languageCodeHL2);
        
        $this->lesson = $lesson;
        $this->fileName = $this->findFileName($lesson, $languageCodeHL1, $languageCodeHL2);
        $this->title = $this->findTitle($lesson, $languageCodeHL1);
        
        $this->setTranslation($this->getTranslationSource());
        $this->studyReferenceInfo = $this->getStudyReferenceInfo($lesson);
        
        $this->bibleReferenceInfo = new BibleReferenceInfoModel();
        $this->bibleReferenceInfo->setFromEntry($this->studyReferenceInfo->getEntry());
        $this->testament = $this->bibleReferenceInfo->getTestament();
        
        $this->bible1 = $this->findBible($languageCodeHL1);
        $this->bible2 = $this->findBible($languageCodeHL2);
        
        $this->setPassage();
        
        $this->qrcode1 = $this->createQRCode($this->biblePassage1->getPassageUrl(), $languageCodeHL1);
        $this->qrcode2 = $this->createQRCode($this->biblePassage2->getPassageUrl(), $languageCodeHL2);
    }

    protected function setTranslation(string $source = 'dbs'): void
    {
        $translation1 = new TranslationModel($this->language1->getLanguageCodeHL(), $source);
        $this->translation1 = $translation1->getTranslationFile();
        
        $translation2 = new TranslationModel($this->language2->getLanguageCodeHL(), $source);
        $this->translation2 = $translation2->getTranslationFile();
    }

    public function getFileName(): string {
        return $this->fileName;
    }

    public function getTranslation1(): string {
        return $this->translation1;
    }

    public function getTranslation2(): string {
        return $this->translation2;
    }

    public function getTemplate(): string {
        return $this->template;
    }

    protected function findBible(string $languageCodeHL, string $testament = 'NT'): BibleModel {
        $bible = new BibleModel($this->bibleRepository);
        $bible->setBestDbsBibleByLanguageCodeHL($languageCodeHL, $testament);
        return $bible;
    }

    public function setPassage(): void {
        $this->biblePassage1 = new PassageSelectController($this->bibleReferenceInfo, $this->bible1);
        $this->biblePassage2 = new PassageSelectController($this->bibleReferenceInfo, $this->bible2);
    }

    private function replacePlaceholders(array $placeholders): void {
        foreach ($placeholders as $key => $value) {
            $this->template = str_replace($key, $value, $this->template);
        }
    }

    public function setBilingualTemplate(string $template): void {
        $file = ROOT_TEMPLATES . $template;
        if (!file_exists($file)) {
            throw new \Exception('Template does not exist: ' . $file);
        }
        
        $this->template = file_get_contents($file);
        $this->createBibleBlock();
        
        $placeholders = [
            self::PLACEHOLDERS['BIBLE_BLOCK'] => $this->bibleBlock,
            self::PLACEHOLDERS['LANGUAGE'] => $this->language1->getName(),
            '||language||' => $this->language2->getName(),
            self::PLACEHOLDERS['BIBLE_REFERENCE'] => $this->biblePassage1->getReferenceLocalLanguage(),
            '||Bible Reference||' => $this->biblePassage2->getReferenceLocalLanguage(),
            self::PLACEHOLDERS['URL'] => $this->biblePassage1->getPassageUrl(),
            '||url||' => $this->biblePassage2->getPassageUrl(),
            self::PLACEHOLDERS['QRCODE1'] => $this->qrcode1,
            '||QrCode2||' => $this->qrcode2,
            self::PLACEHOLDERS['TITLE'] => $this->title,
            self::PLACEHOLDERS['FILENAME'] => $this->getFileName(),
            '{{dir_language1}}' => $this->language1->getDirection(),
            '||dir_language2||' => $this->language2->getDirection(),
            '{{font_language1}}' => $this->language1->getFont(),
            '||font_language2||' => $this->language2->getFont()
        ];

        $this->replacePlaceholders($placeholders);
        $this->setUniqueTemplateValues();
    }

    private function createBibleBlock(): void {
        if ($this->biblePassage1->getPassageText() && $this->biblePassage2->getPassageText()) {
            $bibleBlockController = new BibleBlockController(
                $this->biblePassage1->getPassageText(),
                $this->biblePassage2->getPassageText(),
                $this->bibleReferenceInfo->getVerseRange()
            );
            $this->bibleBlock = $bibleBlockController->getBlock();
        } else {
            $this->createBibleBlockWhenTextMissing();
        }
    }

    private function createBibleBlockWhenTextMissing(): void {
        $this->bibleBlock = $this->showTextOrLink($this->biblePassage1) . $this->showTextOrLink($this->biblePassage2);
    }

    private function showTextOrLink(PassageSelectController $biblePassage): string {
        return $biblePassage->getPassageText() ? $this->showDivText($biblePassage) : $this->showDivLink($biblePassage);
    }

    private function showDivLink(PassageSelectController $biblePassage): string {
        $template = $this->loadTemplateFile(ROOT_TEMPLATES . 'bibleBlockDivLink.template.html');
        return str_replace(
            ['{{dir_language}}', '{{url}}', '{{Bible Reference}}', '{{Bid}}'],
            [$biblePassage->getBibleDirection(), $biblePassage->getPassageUrl(), $biblePassage->getReferenceLocalLanguage(), $biblePassage->getBibleBid()],
            $template
        );
    }

    private function showDivText(PassageSelectController $biblePassage): string {
        $template = $this->loadTemplateFile(ROOT_TEMPLATES . 'bibleBlockDivText.template.html');
        return str_replace(
            ['{{dir_language}}', '{{url}}', '{{Bible Reference}}', '{{Bid}}', '{{passage_text}}'],
            [$biblePassage->getBibleDirection(), $biblePassage->getPassageUrl(), $biblePassage->getReferenceLocalLanguage(), $biblePassage->getBibleBid(), $biblePassage->getPassageText()],
            $template
        );
    }

    private function loadTemplateFile(string $filePath): string {
        if (!file_exists($filePath)) {
            throw new \Exception("Template file does not exist: $filePath");
        }
        return file_get_contents($filePath);
    }

    public function saveBilingualView(): void {
        $filePath = $this->getPathView() . $this->fileName . '.html';
        file_put_contents($filePath, $this->template);
    }
}
