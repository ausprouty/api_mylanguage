<?php

namespace App\Controllers\BibleStudy\Bilingual;

use App\Repositories\BibleRepository;
use App\Repositories\LanguageRepository;
use App\Services\QrCodeGeneratorService;
use App\Traits\DbsFileNamingTrait;
use App\Traits\TemplatePlaceholderTrait;
use App\Controllers\BibleStudy\BibleBlockController;

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

    /**
     * Initializes the BilingualStudyTemplateController.
     *
     * @param LanguageRepository $languageRepository The repository for managing language data.
     * @param BibleRepository $bibleRepository The repository for managing Bible data.
     * @param QrCodeGeneratorService $qrCodeService The service for generating QR codes.
     */
    public function __construct(
        LanguageRepository $languageRepository,
        BibleRepository $bibleRepository,
        QrCodeGeneratorService $qrCodeService
    ) {
        $this->languageRepository = $languageRepository;
        $this->bibleRepository = $bibleRepository;
        $this->qrCodeService = $qrCodeService;
    }

    /**
     * Creates a Bible block for the template by combining passages from both languages.
     * If passages are missing, it falls back to the `createBibleBlockWhenTextMissing` method.
     */
    protected function createBibleBlock(): void {
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

    /**
     * Creates a Bible block when text is missing for a passage.
     */
    private function createBibleBlockWhenTextMissing(): void {
        $this->bibleBlock = $this->showTextOrLink($this->biblePassage1);
    }

    /**
     * Helper method to create a QR code for a specific passage URL and language code.
     *
     * @param string $url The URL to encode in the QR code.
     * @param string $languageCode The language code to append to the file name for the QR code.
     * @return string The URL to the generated QR code image.
     */
    protected function createQrCodeForPassage(string $url, string $languageCode): string {
        $fileName = $this->getFileNamePrefix() . $this->lesson . '-' . $languageCode . '.png';
        $this->qrCodeService->initialize($url, 240, $fileName);
        $this->qrCodeService->generateQrCode();
        
        return $this->qrCodeService->getQrCodeUrl();
    }

    /**
     * Abstract method to define a prefix for the filename. Must be implemented in derived classes.
     *
     * @return string The prefix to use for filenames in the derived class.
     */
    protected abstract function getFileNamePrefix(): string;

    /**
     * Generates QR codes for both Bible passages. Uses the QrCodeGeneratorService
     * to encapsulate QR code generation and maintain modularity.
     */
    protected function generateQrCodes(): void {
        $this->qrcode1 = $this->createQrCodeForPassage($this->biblePassage1->getPassageUrl(), $this->language1->getLanguageCodeHL());
        $this->qrcode2 = $this->createQrCodeForPassage($this->biblePassage2->getPassageUrl(), $this->language2->getLanguageCodeHL());
    }

    /**
     * Sets the filename for the template, using a prefix, lesson, and language codes.
     * It utilizes the `generateFileName` method from `FileNamingTrait`.
     */
    protected function setFileName(): void {
        $this->fileName = $this->generateFileName(
            $this->getFileNamePrefix(),
            $this->lesson,
            $this->language1->getLanguageCodeHL(),
            $this->language2->getLanguageCodeHL()
        );
    }
}
