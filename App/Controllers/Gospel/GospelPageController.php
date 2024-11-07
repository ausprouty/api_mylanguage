<?php
namespace App\Controllers\Gospel;

class GospelPageController{

    public function getBilingualPage($page){
        $file = ROOT_RESOURCES . 'bilingualTracts/' . $page;
        $text = file_get_contents($file);
        return $text;
    }
    
}
