<?php
namespace App\Models;

class AskQuestionModel
{
    private $id;
    private $languageCodeHL;
    private $name;
    private $ethnicName;
    private $url;
    private $contactPage;
    private $languageCodeTracts;
    private $promoText;
    private $promoImage;
    private $tagline;
    private $weight;

    public function __construct($languageCodeHL = '', $name = '', $ethnicName = '', $url = '', $contactPage = '', $languageCodeTracts = '', $promoText = '', $promoImage = '', $tagline = '', $weight = 0)
    {
        $this->languageCodeHL = $languageCodeHL;
        $this->name = $name;
        $this->ethnicName = $ethnicName;
        $this->url = $url;
        $this->contactPage = $contactPage;
        $this->languageCodeTracts = $languageCodeTracts;
        $this->promoText = $promoText;
        $this->promoImage = $promoImage;
        $this->tagline = $tagline;
        $this->weight = $weight;
    }

    // Getters
    public function getId()
    {
        return $this->id;
    }

    public function getLanguageCodeHL()
    {
        return $this->languageCodeHL;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getEthnicName()
    {
        return $this->ethnicName;
    }

    public function getUrl()
    {
        return $this->url;
    }

    public function getContactPage()
    {
        return $this->contactPage;
    }

    public function getLanguageCodeTracts()
    {
        return $this->languageCodeTracts;
    }

    public function getPromoText()
    {
        return $this->promoText;
    }

    public function getPromoImage()
    {
        return $this->promoImage;
    }

    public function getTagline()
    {
        return $this->tagline;
    }

    public function getWeight()
    {
        return $this->weight;
    }

    // Method to set values from a database object
    public function setValues($data)
    {
        $this->id = $data->id;
        $this->languageCodeHL = $data->languageCodeHL;
        $this->name = $data->name;
        $this->ethnicName = $data->ethnicName;
        $this->url = $data->url;
        $this->contactPage = $data->contactPage;
        $this->languageCodeTracts = $data->languageCodeTracts;
        $this->promoText = $data->promoText;
        $this->promoImage = $data->promoImage;
        $this->tagline = $data->tagline;
        $this->weight = $data->weight;
    }
}
