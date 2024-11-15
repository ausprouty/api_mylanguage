<?php
namespace App\Models\Bible;


use App\Repositories\BibleRepository;


class BibleModel {

    private $repository;
    
    private $bid;
    private $source;
    private $externalId;
    private $abbreviation;
    private $volumeName;
    private $volumeNameAlt;
    private $languageCode;
    private $languageName;
    private $languageEnglish;
    private $languageCodeHL;
    private $languageCodeIso;
    private $languageCodeDrupal;
    private $idBibleGateway;
    private $collectionCode;
    private $direction;
    private $numerals;
    private $spacePdf;
    private $noBoldPdf;
    private $format;
    private $text;
    private $audio;
    private $video;
    private $weight;
    private $dateVerified;

 

    public function __construct(BibleRepository $repository){
        $this->repository = $repository;
        $this->initializeDefaultValues();
    }
    private function initializeDefaultValues(){
        $this->bid = 0;
        $this->source = '';
        $this->externalId = '';
        $this->volumeName = '';
        $this->volumeNameAlt = '';
        $this->languageName = '';
        $this->languageEnglish = '';
        $this->languageCodeHL = '';
        $this->languageCodeIso = '';
        $this->idBibleGateway = '';
        $this->collectionCode = '';
        $this->direction = '';
        $this->numerals = '';
        $this->spacePdf = '';
        $this->noBoldPdf = '';
        $this->format = '';
        $this->text = '';
        $this->audio = '';
        $this->video = '';
        $this->weight = 0;
        $this->dateVerified = '';
    }

    public function getBid(){
        return $this->bid;
    }
    public function getCollectionCode(){
        return $this->collectionCode;
    }
    public function getDirection(){
        return $this->direction;
    }
    public function getExternalId(){
        return $this->externalId;
    }
    public function getLanguageCodeHL(){
        return $this->languageCodeHL;
    }
    public function getSource(){
        return $this->source;
    }
    public function getVolumeName(){
        return $this->volumeName;
    }
    
    public function loadBestBibleByLanguageCodeHL($languageCodeHL){
        $data = $this->repository->findBestBibleByLanguageCodeHL($languageCodeHL);
        if ($data) {
            $this->setBibleValues($data);  // Set the model state with retrieved data
        } else {
            throw new \Exception("Best Bible for $languageCodeHL not found");
        }
    }


    public function loadBestDbsBibleByLanguageCodeHL($code, $testament = 'C'){
        $data = $this->repository->findBestDbsBibleByLanguageCodeHL($code, $testament = 'C');
        if ($data) {
            $this->setBibleValues($data);  // Set the model state with retrieved data
        } else {
            throw new \Exception("Best DBS Bible for $code not found");
        }
    }
    public function loadBibleByBid($bid){
        $data = $this->repository->findBibleByBid($bid);
        if ($data) {
            $this->setBibleValues($data);  // Set the model state with retrieved data
        } else {
            throw new \Exception("Bible not found for Bid: $bid");
        }
    }
    public function loadtBibleByExternalId($externalId) {
        $data = $this->repository->findBibleByExternalId($externalId);
        if ($data) {
            $this->setBibleValues($data);  // Set the model state with retrieved data
        } else {
            throw new \Exception("Bible not found for ExternalId: $externalId");
        }
    }
   
    
    protected function setBibleValues($data){
        if (!$data){
            return;
        }
        $this->bid = $data->bid;
        $this->source = $data->source;
        $this->externalId = $data->externalId;
        $this->volumeName = $data->volumeName;
        $this->volumeNameAlt = $data->volumeNameAlt;
        $this->languageName = $data->languageName;
        $this->languageEnglish = $data->languageEnglish;
        $this->languageCodeHL = $data->languageCodeHL;
        $this->idBibleGateway = $data->idBibleGateway;
        $this->collectionCode = $data->collectionCode;
        $this->direction = $data->direction;
        $this->numerals = $data->numerals;
        $this->spacePdf = $data->spacePdf;
        $this->noBoldPdf = $data->noBoldPdf;
        $this->format = $data->format;
        $this->text = $data->text;
        $this->audio = $data->audio;
        $this->video = $data->video;
        $this->weight = $data->weight;
        $this->dateVerified = $data->dateVerified;
    }

    public function setLanguageData($autonym, $language, $iso) {
        $this->languageName = $autonym;
        $this->languageEnglish = $language;
        $this->languageCodeIso = $iso;
    }

    public function resetMediaFlags() {
        $this->text = 0;
        $this->audio = 0;
        $this->video = 0;
    }

    public function determineMediaType($type, $audioTypes, $textTypes, $videoTypes) {
        if (in_array($type, $textTypes)) $this->text = 1;
        if (in_array($type, $audioTypes)) $this->audio = 1;
        if (in_array($type, $videoTypes)) $this->video = 1;
    }

    public function prepareForSave($source, $externalId, $volume, $collectionCode, $format) {
        $this->source = $source;
        $this->externalId = $externalId;
        $this->volumeName = $volume ?? $this->volumeName;
        $this->collectionCode = $collectionCode;
        $this->dateVerified = date('Y-m-d');
        $this->format = $format;
    }
}
