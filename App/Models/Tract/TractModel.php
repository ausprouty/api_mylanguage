<?php
namespace App\Models\Tract;

class TractModel
{
    private $id;
    private $languageCodeHL1;
    private $languageCodeHL2;
    private $name;
    private $webpage;
    private $valid;
    private $validMessage;


    public function __construct(){
        $this->id = '';
        $this->languageCodeHL1 = '';
        $this->languageCodeHL2 = '';
        $this->name = '';
        $this->webpage = '';
        $this->valid = '';
        $this->validMessage = '';
      
    }
    public function getWebpage(){
        return $this->webpage;
    }
}