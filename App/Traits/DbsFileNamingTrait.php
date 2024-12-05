<?php

namespace App\Traits;

use App\Repositories\LanguageRepository;

/**
 * Trait DbsFileNamingTrait
 *
 * Provides methods to generate standardized filenames for resources based on lesson identifiers,
 * language codes, and optional file extensions.
 *
 * @package App\Traits
 */
trait DbsFileNamingTrait
{
  

    /**
     * Returns the prefix used in filenames.
     *
     * Override this method in implementing classes to customize the prefix.
     *
     * @return string The prefix for filenames.
     */
    protected function getFileNamePrefix(): string
    {
        return '';  // Default prefix; can be overridden in each specific controller.
    }

    /**
     * Generates a standardized filename based on the lesson identifier and language codes.
     *
     * @param string $lesson The lesson identifier (e.g., lesson number or name).
     * @param string $languageCodeHL1 The primary language code in HL format (e.g., 'eng00' for English).
     * @param string|null $languageCodeHL2 Optional secondary language code in HL format for bilingual filenames.
     * 
     * @return string The generated filename without extension.
     */
    public function generateFileName(string $lesson, string $languageCodeHL1, string $languageCodeHL2 = null): string
    {
        $prefix = $this->getFileNamePrefix();
        $lang1 = $this->languageRepository->getEnglishNameForLanguageCodeHL($languageCodeHL1);

        if ($languageCodeHL2) {
            $lang2 = $this->languageRepository->getEnglishNameForLanguageCodeHL($languageCodeHL2);
            $fileName = "{$prefix}{$lesson}({$lang1}-{$lang2})";
        } else {
            $fileName = "{$prefix}{$lesson}({$lang1})";
        }

        return str_replace(' ', '_', trim($fileName));
    }

    /**
     * Generates a filename with a `.pdf` extension.
     *
     * @param string $lesson The lesson identifier.
     * @param string $languageCodeHL1 The primary language code in HL format.
     * @param string|null $languageCodeHL2 Optional secondary language code for bilingual filenames.
     * 
     * @return string The generated filename with a `.pdf` extension.
     */
    public function generateFileNamePdf(string $lesson, string $languageCodeHL1, string $languageCodeHL2 = null): string
    {
        return $this->generateFileName($lesson, $languageCodeHL1, $languageCodeHL2) . '.pdf';
    }

    /**
     * Generates a filename with a `.html` extension.
     *
     * @param string $lesson The lesson identifier.
     * @param string $languageCodeHL1 The primary language code in HL format.
     * @param string|null $languageCodeHL2 Optional secondary language code for bilingual filenames.
     * 
     * @return string The generated filename with a `.html` extension.
     */
    public function generateFileNameView(string $lesson, string $languageCodeHL1, string $languageCodeHL2 = null): string
    {
        return $this->generateFileName($lesson, $languageCodeHL1, $languageCodeHL2) . '.html';
    }
}
