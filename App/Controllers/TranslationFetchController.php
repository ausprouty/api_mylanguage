<?php

namespace App\Controllers;
use App\Services\Language\TranslationService;
use App\Utilities\JsonResponse;
use Exception;



class TranslationFetchController {
   
    function webFetchCommonContent(array $args): void {
        try {
  
            // Validate required arguments
            if (!isset($args['study'], $args['languageCodeHL'])) {
                JsonResponse::error('Missing required arguments: study or languageCodeHL');
            }
            // Extract variables from the route arguments
            $study = $args['study'];
            if (isset($args['logic'])){
                $logic = $args['logic'];
            }
            else{
                $logic = null;
            }
            $languageCodeHL = $args['languageCodeHL'];
            $translation = new TranslationService();
            $output = $translation::loadCommonContentTranslation($languageCodeHL, $study, $logic);
            JsonResponse::success($output);
        } catch (Exception $e) {
            // Handle any unexpected errors
            JsonResponse::error($e->getMessage());
        }
    }
    function webFetchInterface(array $args): void {
        try {
         // Validate required arguments
        if (!isset($args['app'], $args['languageCodeHL']) || empty($args['app']) || empty($args['languageCodeHL'])) {
            return JsonResponse::error('Missing required arguments: app or languageCodeHL');
        }
        // Extract variables from the route arguments
        $app = $args['app'];
        $languageCodeHL = $args['languageCodeHL'];
        $translation = new TranslationService();
        $output = $translation::loadInterfaceTranslation( $app, $languageCodeHL);
        JsonResponse::success($output);
        } catch (Exception $e) {
            // Handle any unexpected errors
            JsonResponse::error($e->getMessage());
        }
    

}
