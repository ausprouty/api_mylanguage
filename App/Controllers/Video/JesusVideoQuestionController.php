<?php
namespace App\Controller\Video;

class JesusVideoQuestionController{
    private $template;

    public function __construct(){
        $this->template = null;
    }

    public function getBilingualTemplate ($languageCodeHL1, $languageCodeHL2){
        $template = file_get_contents(ROOT_TEMPLATES . 'bilingualJesusVideoQuestions.template.html');
    }
}