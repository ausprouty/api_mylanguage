<?php
namespace App\Controllers\BiblePassage\BibleGateway;

use App\Services\Database\DatabaseService;
use PDO as PDO;

class BibleGatewayBibleController{

/*

    In Feb 2023 this added 454 languages
    */
    private  $languageCodeIso;
    private  $externalId;
    private  $volumeName;
    private  $languageName;
    private  $defaultBible;

    public function __construct(DatabaseService $databaseService){
        $this->databaseService = $databaseService;
        
        $this->languageCodeIso = 'notSet';
        $this->externalId = '';
        $this->volumeName = '';
        $this->languageName = '';
        $this->defaultBible = '';
    }

    public function import(){
        $filename = ROOT_IMPORT_DATA . 'BibleGatewayBibles.txt';

        if (!file_exists($filename)){
            echo $filename;
            echo (`View source of <a href="https://www.biblegateway.com"</a>and create list of Bibles as follows:
            <pre>
            <option class="lang" value="BG1940">---Български (BG)---</option>
            <option value="BG1940" >1940 Bulgarian Bible (BG1940)</option>
            <option value="BULG" >Bulgarian Bible (BULG)</option>
            <option value="ERV-BG" >Bulgarian New Testament: Easy-to-Read Version (ERV-BG)</option>
            <option value="CBT" >Библия, нов превод от оригиналните езици (с неканоничните книги) (CBT)</option>
            <option value="BOB" >Библия, синодално издание (BOB)</option>
            <option value="BPB" >Библия, ревизирано издание (BPB)</option>
            <option class="spacer" value="BPB">&nbsp;</option>
            </pre>`);
            return;

        }
        $datafile = file_get_contents($filename);
        $count = 0;
        $languageCodeIso = '';
        $lines = explode("\n", $datafile);
        foreach ($lines as $line){
            if (strpos($line, 'class="lang"') !== FALSE){
                $this->setLanguageName($line);
                $this->setLanguageCodeIso($line);
                $this->setDefaultBible($line);
            }
            elseif  (strpos($line, 'class="spacer"') == FALSE){
                $this->setExternalId($line);
                $bid = $this->recordExists();
                if ($bid){
                    $this->updateVerified($bid);
                    //Todo: remove the following two lines after setting my own defaults
                    $this->updateLanguage($bid);
                    $this->updateWeight($bid);
                }
                else{
                    $this->insertRecord();
                    $count++;
                }
            }
        }
        return $count;
    }

    private function setLanguageName($line){
        // <option class="lang" value="BG1940">---Български (BG)---</option>
        $pos = strpos($line, '>-') + 2;
        $new = substr ($line, $pos);
        $pos = strpos($new, '(') -1;
        $new = substr($new, 0, $pos);
        $new = str_replace('-', '', $new);
        $new = trim ($new);
        $this->languageName = $new;
    }
    private function setDefaultBible($line){
        // <option class="lang" value="BG1940">---Български (BG)---</option>
        $pos = strpos($line, 'value="') + 7;
        $new = substr ($line, $pos);
        $pos = strpos($new, '"');
        $new = substr($new, 0, $pos);
        $this->defaultBible = $new;
    }
    private function setLanguageCodeIso($line){
        $pos_last = strrpos($line, '(');
        $new = substr($line, $pos_last);
        $pos_last = strpos($new, ')')-1;
        $new = substr($new, 1, $pos_last);
        $try= strtolower($new);
        if ($this->tryLanguageCodeIso($try)){
            $this->languageCodeIso = $try;
        }
        else {
            $languageCode = $this->tryLanguageCodeGoogle($try);
            if ($languageCode ){
                 $this->languageCodeIso = $languageCode ;
            }
            else {
                $languageCode = $this->tryLanguageCodeBrowser($try);
                if ($languageCode ){
                    $this->languageCodeIso = $languageCode ;
                }
                else{
                    $this->addNewLanguage($try);
                }
            }
        }
    }
    private function tryLanguageCodeIso($try){
        $databaseService = new DatabaseService();
        $query = "SELECT languageCodeIso FROM hl_languages
            WHERE languageCodeIso = :languageCodeIso LIMIT 1";
        $params = array(':languageCodeIso'=> $try, 
        );
        $statement = $databaseService->executeQuery($query, $params);
        $languageCodeIso = $statement->fetch(PDO::FETCH_COLUMN);
       return $languageCodeIso;
    }
    private function tryLanguageCodeGoogle($try){
        $databaseService = new DatabaseService();
        $query = "SELECT languageCodeIso FROM hl_languages
            WHERE languageCodeGoogle = :languageCode LIMIT 1";
        $params = array(':languageCode'=> $try, 
        );
        $statement = $databaseService->executeQuery($query, $params);
        $languageCodeIso = $statement->fetch(PDO::FETCH_COLUMN);
       return $languageCodeIso;
    }
    private function tryLanguageCodeBrowser($try){
        $databaseService = new DatabaseService();
        $query = "SELECT languageCodeIso FROM hl_languages
            WHERE languageCodeBrowser = :languageCode LIMIT 1";
        $params = array(':languageCode'=> $try, 
        );
        $statement = $databaseService->executeQuery($query, $params);
        $languageCodeIso = $statement->fetch(PDO::FETCH_COLUMN);
       return $languageCodeIso;
    }
    private function setExternalId($line){
        $pos = strpos($line, '>') +1;
        $new = substr($line, $pos);
        $pos = strpos($new, '<') -1;
        $new = substr($new, 0, $pos);
        $pos = strrpos($new, '(');
        $this->volumeName = trim(substr($new, 0, $pos-1));
        $this->externalId = substr($new, $pos +1);

    }
    private function recordExists(){
        $databaseService = new DatabaseService();
        $query = "SELECT bid FROM bibles 
            WHERE source = :biblegateway AND
            externalId = :externalId LIMIT 1";
        $params = array(':biblegateway'=>'bible_gateway', 
            ':externalId' => $this->externalId, 
        );
        try {
            $statement = $databaseService->executeQuery($query, $params);
            $bid = $statement->fetch(PDO::FETCH_COLUMN);
            return $bid;
        } catch (Exception $e) {
            echo "Error: " . $e->getMessage();
            return null;
        }
    }
    private function updateVerified($bid){
        $verified = date('Y-m-d');
        $databaseService = new DatabaseService();
        $query = "UPDATE bibles 
            SET dateVerified = :verified
            WHERE bid = :bid 
            LIMIT 1";
        $params = array(':verified'=>$verified, 
            ':bid' => $bid, 
        );
        $statement = $databaseService->executeQuery($query, $params);
    }
    private function updateLanguage($bid){
        $databaseService = new DatabaseService();
        $query = "UPDATE bibles 
            SET languageCodeIso = :languageCodeIso,
            languageName = :languageName
            WHERE bid = :bid 
            LIMIT 1";
        $params = array(
            ':languageCodeIso'=> $this->languageCodeIso, 
            ':languageName' => $this->languageName,
            ':bid' => $bid, 
        );
        $statement = $databaseService->executeQuery($query, $params);
    }


    private function insertRecord(){
        $verified = date('Y-m-d');
        $weight = 0;
        if ($this->externalId == $this->defaultBible){
            $weight = 9;
        }
        $databaseService = new DatabaseService();
        $query = "INSERT INTO bibles 
        (source, externalId, volumeName, languageName, languageCodeIso, 
        collectionCode, format, text, weight , dateVerified) 
        VALUES 
        (:source, :externalId, :volumeName, :languageCodeIso, 
        :collectionCode, :format, :text, :weight, :dateVerified)";
        $params = array(
            ':source' => 'bible_gateway', 
            ':externalId' => $this->externalId, 
            ':volumeName' => $this->volumeName,
            ':languageName' => $this->languageName, 
            ':languageCodeIso' => $this->languageCodeIso, 
            ':collectionCode' => 'C', 
            ':format' =>'text', 
            ':text' => 'Y', 
            ':weight' => $weight, 
            ':dateVerified' => $verified
        );
        $statement = $databaseService->executeQuery($query, $params);

    }
    private function addNewLanguage($try){
        $databaseService = new DatabaseService();
        $query = "INSERT INTO hl_languages 
        (languageCodeHL, languageCodeIso, ethnicName ) 
        VALUES 
        (:languageCodeHL, :languageCodeIso, :ethnicName) ";
        $params = array(
            ':languageCodeHL' => $try . date('y'), 
            ':languageCodeIso' => $try, 
            ':ethnicName' => $this->languageName,
        );
        $statement = $databaseService->executeQuery($query, $params);
    }
    private function updateWeight($bid){
        $weight = 0;
        if ($this->externalId == $this->defaultBible){
            $weight = 9;
        }
        $databaseService = new DatabaseService();
        $query = "UPDATE bibles 
            SET weight = :weight
            WHERE bid = :bid 
            LIMIT 1";
        $params = array(
            ':weight'=> $weight,
            ':bid' => $bid, 
        );
        $statement = $databaseService->executeQuery($query, $params);
    }
}