<?php
namespace App\Controller\BibleStudy\Bilingual;

use App\Controllers\BibleStudy\BibleBlockController as BibleBlockController;
use App\Controllers\BiblePassage\PassageSelectController as PassageSelectController;
use App\Model\Bible\BibleModel as BibleModel;
use App\Model\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Model\Language\TranslationModel as TranslationModel;
use App\Model\Language\LanguageModel as LanguageModel;

abstract class BilingualStudyTemplateController
{
    protected  $bible1;
    protected  $bible2;
    protected  $bibleBlock;
    protected  $biblePassage1;
    protected  $biblePassage2;
    protected  $bibleReferenceInfo;
    protected  $testament;
    protected  $fileName;  // name of pdf or view file
    protected  $language1; // langauge object
    protected  $language2;
    protected  $lesson;
    protected  $qrcode1;
    protected  $qrcode2;
    protected  $template;
    protected  $studyReferenceInfo;
    protected  $title;
    protected  $translation1;
    protected  $translation2;

    abstract protected function createQRCode($url, $languageCodeHL);
    abstract static function findFileName($lesson, $languageCodeHL1, $languageCodeHL2);
    abstract static function findFileNamePdf($lesson, $languageCodeHL1, $languageCodeHL2);
    abstract protected function findTitle($lesson, $languageCodeHL1);
    abstract protected function getBilingualTemplateName();
    abstract static function getPathPdf();
    abstract static function getUrlPdf();
    abstract static function getPathView();
    abstract protected function getStudyReferenceInfo($lesson);
    abstract protected function getTranslationSource();
    abstract protected function setFileName();
	abstract protected function setUniqueTemplateValues();
   
    public function __construct( string $languageCodeHL1, string $languageCodeHL2, $lesson)
    {   
        $this->language1 = new LanguageModel();
        $this->language1->findOneByLanguageCodeHL( $languageCodeHL1);
        $this->language2 = new LanguageModel();
        $this->language2->findOneByLanguageCodeHL( $languageCodeHL2);
        $this->bibleBlock = '';
        $this->biblePassage1 = '';
        $this->biblePassage2 = '';
        $this->lesson = $lesson;
        $this->fileName = $this->findFileName( $lesson, $languageCodeHL1, $languageCodeHL2);
        $this->title = $this->findTitle($lesson, $languageCodeHL1);
        $this->setTranslation($this->getTranslationSource());
        $this->studyReferenceInfo = $this->getStudyReferenceInfo($lesson);
        $this->bibleReferenceInfo =new BibleReferenceInfoModel();
        $this->bibleReferenceInfo->setFromEntry($this->studyReferenceInfo->getEntry());
        $this->testament = $this->bibleReferenceInfo->getTestament();
        $this->bible1 = $this->findBibleOne($languageCodeHL1, $this->testament);
        $this->bible2 = $this->findBibleTwo($languageCodeHL2, $this->testament);
        $this->setPassage($this->bibleReferenceInfo);
        $this->qrcode1 = $this->createQrCode( $this->biblePassage1->getPassageUrl(), $languageCodeHL1);
        $this->qrcode2 = $this->createQrCode( $this->biblePassage2->getPassageUrl(), $languageCodeHL2);
    }
    protected function setTranslation ($source = 'dbs') {
        $translation1 = new TranslationModel($this->language1->getLanguageCodeHL(), $source);
        $this->translation1 = $translation1->getTranslationFile();
        $translation2 = new TranslationModel($this->language2->getLanguageCodeHL(), $source);
        $this->translation2 =  $translation2->getTranslationFile();
    }
    

    public function getFileName(){
        return $this->fileName;
    }
    public function getTranslation1(){
        return $this->translation1;
    }
    public function getTranslation2(){
        return $this->translation2;
    }
    public function getTemplate(){
        return $this->template;
    }
    
    protected function findBibleOne($languageCodeHL1, $testament='NT')
    {
        $bible = new BibleModel();
        $bible->setBestDbsBibleByLanguageCodeHL($languageCodeHL1, $testament);
        return $bible;
    }
    protected function findBibleTwo($languageCodeHL2, $testament= 'NT')
    {
        $bible = new BibleModel();
        $bible->setBestDbsBibleByLanguageCodeHL($languageCodeHL2, $testament);
        return $bible;
    }
    public function setPassage(BibleReferenceInfoModel $bibleReferenceInfo)
    {
        $this->bibleReferenceInfo = $bibleReferenceInfo;
        $this->biblePassage1= new PassageSelectController ($this->bibleReferenceInfo, $this->bible1);
        $this->biblePassage2= new PassageSelectController ($this->bibleReferenceInfo, $this->bible2);
    }
    private function fillPlaceHolder($placeholder, $value){
        $this->template = str_replace($placeholder, $value, $this->template);
    }
    private function fillPlaceHolderSpanLanguage1($placeholder, $value){
        $span = '<span dir="{{dir_language1}}" style="font-family:{{font_language1}};" >';
        $span .= $value . '</span>';
        $this->template = str_replace($placeholder, $span, $this->template);
    }
    private function fillPlaceHolderSpanLanguage2($placeholder, $value){
        $span = '<span dir="||dir_language2||" style="font-family:||font_language2||;" >';
        $span .= $value . '</span>';
        $this->template = str_replace($placeholder, $span, $this->template);
    }
    public function setBilingualTemplate($template)
    {
        $file = ROOT_TEMPLATES . $template;
        if (!file_exists($file)){
            writeLogError('BilingualStudyTemplateController-124', 'template does not exist');
            return 'Template does not exist';
        }
        $this->template = file_get_contents($file);
        $this->createBibleBlock();
        $this->fillPlaceHolderSpanLanguage1('{{Bible Block}}', $this->bibleBlock);
        $this->fillPlaceHolderSpanLanguage1('{{language}}', $this->language1->getName());
        $this->fillPlaceHolder('||language||', $this->language2->getName());
        $this->fillPlaceHolder('{{Bible Reference}}', $this->biblePassage1->getReferenceLocalLanguage());
        $this->fillPlaceHolder('||Bible Reference||', $this->biblePassage2->getReferenceLocalLanguage());
        $this->fillPlaceHolder('{{url}}', $this->biblePassage1->getPassageUrl());
        $this->fillPlaceHolder('||url||', $this->biblePassage2->getPassageUrl());
        $this->fillPlaceHolder('{{QrCode1}}', $this->qrcode1);
        $this->fillPlaceHolder('||QrCode2||', $this->qrcode2);
        $this->fillPlaceHolderSpanLanguage1('{{Title}}', $this->title);
        $this->fillPlaceHolderSpanLanguage1('{{filename}}', $this->getFileName());
        $this->fillPlaceHolderSpanLanguage1('{{Video Block}}', '');
       
        foreach ($this->translation1 as $key => $value){
            $find= '{{' . $key . '}}';
            $this->fillPlaceHolderSpanLanguage1($find, $value);
        }
        foreach ($this->translation2 as $key => $value){
            $find= '||' . $key . '||';
            $this->fillPlaceHolderSpanLanguage2($find, $value);
        }
        $this->fillPlaceHolder('{{dir_language1}}', $this->language1->getDirection());
        $this->fillPlaceHolder('||dir_language2||', $this->language2->getDirection());
        $this->fillPlaceHolder('{{font_language1}}', $this->language1->getFont());
        $this->fillPlaceHolder('||font_language2||', $this->language2->getFont());
        $this->setUniqueTemplateValues();
    }
    private function createBibleBlock(){
        // a blank record is NULL
        if ($this->biblePassage1->getPassageText() !==  NULL 
            && $this->biblePassage2->getPassageText() !== NULL
            && $this->biblePassage1->getPassageText() !==  '' 
            && $this->biblePassage2->getPassageText() !== '')
            {
           $bibleBlock = new BibleBlockController(
                    $this->biblePassage1->getPassageText(),
                    $this->biblePassage2->getPassageText(), 
                    $this->bibleReferenceInfo->getVerseRange()
            );
            $this->bibleBlock =$bibleBlock->getBlock();
        }
        else{
            $this->createBibleBlockWhenTextMissing();
        }
    }
    private function createBibleBlockWhenTextMissing(){
        $this->bibleBlock = '';
        if ($this->biblePassage2->getPassageText() !== NULL
            && $this->biblePassage2->getPassageText() !== ''){
            $this->bibleBlock .= $this->showTextOrLink($this->biblePassage1);
            $this->bibleBlock .= $this->showTextOrLink($this->biblePassage2);
        }
        else{
            $this->bibleBlock .= $this->showTextOrLink($this->biblePassage2);
            $this->bibleBlock .= $this->showTextOrLink($this->biblePassage1);
        }
    }
    private function showTextOrLink($biblePassage){
        if ($biblePassage->getPassageText() == NULL){
            return $this->showDivLink($biblePassage); 
        }else{
            return $this->showDivText($biblePassage);
        }
    }
    private function showDivLink($biblePassage){
        $template = file_get_contents(ROOT_TEMPLATES . 'bibleBlockDivLink.template.html');
        $existing = array(
            '{{dir_language}}',
            '{{url}}',
            '{{Bible Reference}}',
            '{{Bid}}'
        );
        $new = array(
            $biblePassage->getBibleDirection(),
            $biblePassage->passageUrl,
            $biblePassage->referenceLocalLanguage,
            $biblePassage->getBibleBid()
        );
        $template = str_replace($existing, $new, $template);
        return $template;

    }
    private function showDivText($biblePassage){
        $template = file_get_contents(ROOT_TEMPLATES . 'bibleBlockDivText.template.html');
        $existing = array(
            '{{dir_language}}',
            '{{url}}',
            '{{Bible Reference}}',
            '{{Bid}}',
            '{{passage_text}}'
        );
        $new = array(
            $biblePassage->getBibleDirection(),
            $biblePassage->passageUrl,
            $biblePassage->referenceLocalLanguage,
            $biblePassage->getBibleBid(),
            $biblePassage->getPassageText()
        );
        $template = str_replace($existing, $new, $template);
        return $template;
    }
    
    

    public function saveBilingualView(){
        $filePath= $this->getPathView() . $this->fileName .'.html';
        $text = $this->template;
        file_put_contents($filePath, $text);
    }
}