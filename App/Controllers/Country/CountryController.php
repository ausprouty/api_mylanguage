<?php

namespace App\Controllers\Country;

use App\Services\Database\DatabaseService
use PDO as PDO;


Class CountryController extends Country {

    static function getCountryByIsoCode($countryCodeIso){
        $dbService = new DatabaseService();
        $query = "SELECT *
                  FROM country_locations 
                  WHERE countryCodeIso = :countryCodeIso";
                  $params = array(':countryCodeIso'=> $countryCodeIso);
        try {
            $statement = $dbService->executeQuery($query, $params);
            $data = $statement->fetch(PDO::FETCH_OBJECT);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    static function getCountryByIsoCode3($countryCodeIso3){
        $dbService = new DatabaseService();
        $query = "SELECT *
                  FROM country_locations 
                  WHERE countryCodeIso3 = :countryCodeIso3";
                  $params = array(':countryCodeIso3'=> $countryCodeIso3);
        try {
            $statement = $dbService->executeQuery($query, $params);
            $data = $statement->fetch(PDO::FETCH_OBJECT);
            return $data;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    protected function updateCountryCodeIso3($countryCodeIso, $countryCodeIso3){
        $dbService = new DatabaseService();
        $query = "UPDATE country_locations 
                SET countryCodeIso3 = :countryCodeIso3
                  WHERE countryCodeIso = :countryCodeIso";
                  $params = array(
                    ':countryCodeIso3'=> $countryCodeIso3, 
                    ':countryCodeIso'=> $countryCodeIso)
                ;
        try {
            $statement = $dbService->executeQuery($query, $params);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    protected function updateCountryNamefromCodeIso($countryCodeIso, $countryName){
        $dbService = new DatabaseService();
        $query = "UPDATE country_locations 
                SET countryName = :countryName
                  WHERE countryCodeIso = :countryCodeIso";
                  $params = array(
                    ':countryName'=> $countryName, 
                    ':countryCodeIso'=> $countryCodeIso)
                ;
        try {
            $statement = $dbService->executeQuery($query, $params);
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }


}