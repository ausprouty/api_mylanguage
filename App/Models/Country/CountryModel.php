<?php
namespace App\Models\Country
;
class CountryModel
{
    private $countryCodeIso;
    private $countryCodeIso3;
    private $countryNameEnglish;
    private $countryName;
    private $continentCode;
    private $contenentName;
    private $inEuropeanUnion;

    /**
     * Populates the model with data from an associative array.
     *
     * @param array $data Associative array with keys matching property names.
     */
    public function populate(array $data): void
    {
        foreach ($data as $key => $value) {
            if (property_exists($this, $key)) {
                $this->$key = $value;
            }
        }
    }

}