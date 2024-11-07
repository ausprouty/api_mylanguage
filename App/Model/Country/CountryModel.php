<?php
namespace App\Model\Country
;
class Country
{
    private $countryCodeIso;
    private $countryCodeIso3;
    private $countryNameEnglish;
    private $countryName;
    private $continentCode;
    private $contenentName;
    private $inEuropeanUnion;

    public function __construct(){
        $this->countryCodeIso= '';
        $this->countryCodeIso3= '';
        $this->countryNameEnglish= '';
        $this->countryName= '';
        $this->continentCode= '';
        $this->contenentName= '';
        $this->inEuropeanUnion= ''; 
    }
}