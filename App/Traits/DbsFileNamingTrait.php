<?php

namespace App\Traits;

use App\Models\Language\LanguageModel;

/**
 * Trait DbsFileNamingTrait
 *
 * Provides methods to generate standardized filenames for resources based on lesson identifiers,
 * language codes, and optional file extensions. This trait is designed to work for both
 * monolingual (single language) and bilingual (two languages) study resources.
 *
 * It includes methods for generating plain filenames as well as filenames with `.pdf` and `.html` extensions.
 * The prefix used in the filename can be customized by overriding the `getFileNamePrefix` method
 * in the implementing class.
 *
 * @package App\Traits
 */
trait DbsFileNamingTrait
{
    /**
     * Returns the prefix used in filenames.
     *
     * Override this method in implementing classes to customize the prefix.
     * For example, "LifePrinciple" or "Leadership".
     *
     * @return string The prefix for filenames.
     */
    protected static function getFileNamePrefix(): string {
        return '';  // Default prefix; can be overridden in each specific controller.
    }

    /**
     * Generates a standardized filename based on the lesson identifier and language codes.
     *
     * @param string $lesson The lesson identifier (e.g., lesson number or name).
     * @param string $languageCodeHL1 The primary language code in HL format (e.g., 'en' for English).
     * @param string|null $languageCodeHL2 Optional secondary language code in HL format for bilingual filenames.
     * 
     * @return string The generated filename without extension.
     */
    public static function generateFileName(string $lesson, string $languageCodeHL1, string $languageCodeHL2 = null): string {
        $prefix = static::getFileNamePrefix();
        $lang1 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL1);
        
        if ($languageCodeHL2) {
            // Bilingual filename: includes both language names.
            $lang2 = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL2);
            $fileName = "{$prefix}{$lesson}({$lang1}-{$lang2})";
        } else {
            // Monolingual filename: includes only the primary language name.
            $fileName = "{$prefix}{$lesson}({$lang1})";
        }

        return str_replace(' ', '_', trim($fileName));
    }

    /**
     * Generates a filename with a `.pdf` extension for the given lesson and language codes.
     *
     * @param string $lesson The lesson identifier.
     * @param string $languageCodeHL1 The primary language code in HL format.
     * @param string|null $languageCodeHL2 Optional secondary language code for bilingual filenames.
     * 
     * @return string The generated filename with a `.pdf` extension.
     */
    public static function generateFileNamePdf(string $lesson, string $languageCodeHL1, string $languageCodeHL2 = null): string {
        return self::generateFileName($lesson, $languageCodeHL1, $languageCodeHL2) . '.pdf';
    }

    /**
     * Generates a filename with a `.html` extension for the given lesson and language codes.
     *
     * @param string $lesson The lesson identifier.
     * @param string $languageCodeHL1 The primary language code in HL format.
     * @param string|null $languageCodeHL2 Optional secondary language code for bilingual filenames.
     * 
     * @return string The generated filename with a `.html` extension.
     */
    public static function generateFileNameView(string $lesson, string $languageCodeHL1, string $languageCodeHL2 = null): string {
        return self::generateFileName($lesson, $languageCodeHL1, $languageCodeHL2) . '.html';
    }
}
