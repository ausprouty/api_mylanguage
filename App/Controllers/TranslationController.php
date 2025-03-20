<?php

namespace App\Controllers;
use App\Services\Language\TranslationService;
use App\Utilities\JsonResponse;
use Exception;



class TranslationController {
   
    function webFetchCommonContent(array $args): void {
        try {
           
            // Validate required arguments
            if (!isset($args['study'], $args['languageCodeHL'])) {
                JsonResponse::error('Missing required arguments: study or languageCodeHL');
            }
    
            // Extract variables from the route arguments
            $study = $args['study'];
            if ($args['logic']){
                $logic = $args['logic'];
            }
            else{
                $logic = null;
            }
            $languageCodeHL = $args['languageCodeHL'];
    
            // Load translation from Resources/tranlations/languages
            $translation = new TranslationService();
            $output = $translation::loadTranslation($languageCodeHL, $study, $logic);
            // Return success response
            JsonResponse::success($output);
        } catch (Exception $e) {
            // Handle any unexpected errors
            JsonResponse::error($e->getMessage());
        }
    }
    

}
