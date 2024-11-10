<?php

namespace App\Traits;

use App\Models\Language\LanguageModel;

trait MonolingualFileNamingTrait
{
    public static function findFileName(string $lesson, string $languageCodeHL): string {
        $lang = LanguageModel::getEnglishNameFromCodeHL($languageCodeHL);
        $fileName = self::getFileNamePrefix() . $lesson . '(' . $lang . ')';
        return str_replace(' ', '_', trim($fileName));
    }

    public static function findFileNamePdf(string $lesson, string $languageCodeHL): string {
        return self::findFileName($lesson, $languageCodeHL) . '.pdf';
    }

    public static function findFileNameView(string $lesson, string $languageCodeHL): string {
        return self::findFileName($lesson, $languageCodeHL) . '.html';
    }

    abstract protected static function getFileNamePrefix(): string;
}
