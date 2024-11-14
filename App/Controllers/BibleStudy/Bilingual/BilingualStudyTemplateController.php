<?php

namespace App\Controllers\BibleStudy\Bilingual;

use App\Repositories\BibleRepository;
use App\Repositories\LanguageRepository;
use App\Services\QrCodeGeneratorService;
use App\Traits\DbsFileNamingTrait;
use App\Traits\TemplatePlaceholderTrait;
use App\Controllers\BibleStudy\BibleBlockController;
use App\Configuration\Config;

/**
 * Class BilingualStudyTemplateController
 *
 * This abstract controller provides a foundation for managing bilingual Bible study templates.
 * It includes methods for setting filenames, generating QR codes, creating Bible text blocks,
 * and handling template placeholders. The QR code generation leverages a dedicated service,
 * `QrCodeGeneratorService`, for more modular functionality.
 *
 * @package App\Controllers\BibleStudy\Bilingual
 */
abstract class BilingualStudyTemplateController
{
    use DbsFileNamingTrait, TemplatePlaceholderTrait;

    protected LanguageRepository $languageRepository;
    protected BibleRepository $bibleRepository;
    protected QrCodeGeneratorService $qrCodeService;
    protected BibleBlockController $bibleBlockController;
    protected string $fileName;
    protected string $bibleBlock;
    protected string $qrcode1;
    protected string $qrcode2;
    protected string $lesson;
    protected $language1;
    protected $language2;
    protected $biblePassage1;
    protected $biblePassage2;
    protected $bibleReferenceInfo;

    public function __construct(
        LanguageRepository $languageRepository,
        BibleRepository $bibleRepository,
        QrCodeGeneratorService $qrCodeService,
        BibleBlockController $bibleBlockController
    ) {
        $this->languageRepository = $languageRepository;
        $this->bibleRepository = $bibleRepository;
        $this->qrCodeService = $qrCodeService;
        $this->bibleBlockController = $bibleBlockController;
    }

    protected function createBibleBlock(): void
    {
        if ($this->biblePassage1->getPassageText() && $this->biblePassage2->getPassageText()) {
            $this->bibleBlockController->load(
                $this->biblePassage1->getPassageText(),
                $this->biblePassage2->getPassageText(),
                $this->bibleReferenceInfo->getVerseRange()
            );
            $this->bibleBlock = $this->bibleBlockController->getBlock();
        } else {
            $this->createBibleBlockWhenTextMissing();
        }
    }

    private function createBibleBlockWhenTextMissing(): void
    {
        $this->bibleBlock = $this->showTextOrLink($this->biblePassage1);
    }

    protected function createQrCodeForPassage(string $url, string $languageCode): string
    {
        $fileName = $this->getFileNamePrefix() . $this->lesson . '-' . $languageCode . '.png';
        $this->qrCodeService->initialize($url, 240, $fileName);
        $this->qrCodeService->generateQrCode();

        return $this->qrCodeService->getQrCodeUrl();
    }

    protected abstract function getFileNamePrefix(): string;

    protected function generateQrCodes(): void
    {
        $this->qrcode1 = $this->createQrCodeForPassage($this->biblePassage1->getPassageUrl(), $this->language1->getLanguageCodeHL());
        $this->qrcode2 = $this->createQrCodeForPassage($this->biblePassage2->getPassageUrl(), $this->language2->getLanguageCodeHL());
    }

    private function showTextOrLink($biblePassage): string
    {
        return $biblePassage->getPassageText() === null
            ? $this->showDivLink($biblePassage)
            : $this->showDivText($biblePassage);
    }

    private function showDivLink($biblePassage): string
    {
        $templatePath = Config::get('ROOT_TEMPLATES') . 'bibleBlockDivLink.twig';
        $template = file_get_contents($templatePath);

        $existing = ['{{dir_language}}', '{{url}}', '{{Bible Reference}}', '{{Bid}}'];
        $new = [
            $biblePassage->getBibleDirection(),
            $biblePassage->passageUrl,
            $biblePassage->referenceLocalLanguage,
            $biblePassage->getBibleBid()
        ];
        return str_replace($existing, $new, $template);
    }

    private function showDivText($biblePassage): string
    {
        $templatePath = Config::get('ROOT_TEMPLATES') . 'bibleBlockDivText.twig';
        $template = file_get_contents($templatePath);

        $existing = ['{{dir_language}}', '{{url}}', '{{Bible Reference}}', '{{Bid}}', '{{passage_text}}'];
        $new = [
            $biblePassage->getBibleDirection(),
            $biblePassage->passageUrl,
            $biblePassage->referenceLocalLanguage,
            $biblePassage->getBibleBid(),
            $biblePassage->getPassageText()
        ];
        return str_replace($existing, $new, $template);
    }

    protected function setFileName(): void
    {
        $this->fileName = $this->generateFileName(
            $this->getFileNamePrefix(),
            $this->lesson,
            $this->language1->getLanguageCodeHL(),
            $this->language2->getLanguageCodeHL()
        );
    }
}
