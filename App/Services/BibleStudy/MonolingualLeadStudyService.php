<?php

namespace App\Services\BibleStudy;

use App\Services\BibleStudy\MonolingualStudyService;

class MonolingualLeadStudyService extends MonolingualStudyService{

    public function getTwigTranslationArray(): array {
        // Get the array from the parent class
        $parentTranslations = parent::getTwigTranslationArray();
        $data =  $this->translationService->loadTranslation($this->languageCodeHL1, $this->study);
        // Add additional translations specific to MonolingualLifeStudyService
        $question_twig_key = $this->studyReferenceInfo->getQuestionTwigKey();
       
        
        $additionalTranslations = [
            'topic_sentence' =>  $data[$question_twig_key] ,
           
        ];
        // Merge the parent and additional translations
        return array_merge($parentTranslations, $additionalTranslations);
    }
}