<?php

namespace App\Controllers\BibleStudy\Bilingual;

use App\Models\BibleStudy\LifePrincipleReferenceModel;
use App\Controllers\BibleStudy\LifeStudyController;

/**
 * Class BilingualLifeTemplateController
 *
 * Controller for managing Life Principle Bible study templates in a bilingual format.
 * Extends BilingualStudyTemplateController to support specific functionalities for Life Principle studies.
 *
 * @package App\Controllers\BibleStudy\Bilingual
 */
class BilingualLifeTemplateController extends BilingualStudyTemplateController
{
    /**
     * Returns the prefix for filenames specific to Life Principle templates.
     *
     * @return string Prefix for Life Principle templates.
     */
    protected function getFileNamePrefix(): string
    {
        return 'LifePrinciple';
    }

    /**
     * Finds and returns the title for a Life Principle study based on the lesson and language code.
     *
     * @param string $lesson The lesson identifier.
     * @param string $languageCodeHL1 The primary language code for the title.
     * @return string The title of the Life Principle study.
     */
    protected function findTitle(string $lesson, string $languageCodeHL1): string
    {
        return LifeStudyController::getTitle($lesson, $languageCodeHL1);
    }

    /**
     * Retrieves the study reference information for a specific lesson.
     * Uses LifePrincipleReferenceModel to fetch details for the lesson.
     *
     * @param string $lesson The lesson identifier.
     * @return LifePrincipleReferenceModel The study reference information for the Life Principle study.
     */
    protected function getStudyReferenceInfo(string $lesson): LifePrincipleReferenceModel
    {
        $studyReferenceInfo = new LifePrincipleReferenceModel();
        $studyReferenceInfo->setLesson($lesson);
        return $studyReferenceInfo;
    }

    /**
     * Specifies the translation source for Life Principle templates.
     *
     * @return string Translation source identifier.
     */
    protected function getTranslationSource(): string
    {
        return 'life';
    }

    /**
     * Sets unique template values specific to Life Principle studies.
     * Populates placeholders for "Topic Sentence" with translations from both languages.
     */
    protected function setUniqueTemplateValues(): void
    {
        $question = $this->studyReferenceInfo->getQuestion();

        // Replace placeholders in the template with translations
        $this->replaceTemplateValues('{{Topic Sentence}}', $this->getTranslation1(), $question);
        $this->replaceTemplateValues('||Topic Sentence||', $this->getTranslation2(), $question);
    }

    /**
     * Helper method to replace placeholders in the template with translations
     * for a given question key.
     *
     * @param string $placeholder The placeholder in the template to replace.
     * @param array $translations Array of translations to search for the question key.
     * @param string $question The key for the question to retrieve from translations.
     */
    private function replaceTemplateValues(string $placeholder, array $translations, string $question): void
    {
        if (array_key_exists($question, $translations)) {
            $this->template = str_replace($placeholder, $translations[$question], $this->template);
        }
    }
}
