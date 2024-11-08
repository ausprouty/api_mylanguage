<?php
namespace App\Controllers\BibleStudy\Monolingual;

use App\Controllers\BiblePassage\PassageSelectController as PassageSelectController;
use App\Models\Bible\BibleModel as BibleModel;
use App\Models\Bible\BibleReferenceInfoModel as BibleReferenceInfoModel;
use App\Models\Language\TranslationModel as TranslationModel;
use App\Models\Language\LanguageModel as LanguageModel;
use App\Repositories\BibleRepository;

abstract class MonolingualStudyTemplateController
{
    protected  $bible1;
    protected  $bibleBlock;
    protected  $biblePassage1;
    protected  $bibleReferenceInfo;
    protected  $testament;
    protected  $fileName;  // name of pdf or view file
    protected  $language1; // langauge object
    protected  $lesson;
    protected  $qrcode1;
    protected  $template;
    protected  $studyReferenceInfo;
    protected  $title;
    protected  $translation1;

    abstract protected function createQRCode($url, $languageCodeHL);
    abstract static function findFileName($lesson, $languageCodeHL1);
    abstract static function findFileNamePdf($lesson, $languageCodeHL1);
    abstract static function findFileNameView($lesson, $languageCodeHL1);
    abstract protected function findTitle($lesson, $languageCodeHL1);
    abstract protected function getMonolingualPdfTemplateName();
    abstract protected function getMonolingualViewTemplateName();
    abstract static function getPathPdf();
    abstract static function getUrlPdf();
    abstract static function getPathView();
    abstract protected function getStudyReferenceInfo($lesson);
    abstract protected function getTranslationSource();
    abstract protected function setFileName();
	abstract protected function setUniqueTemplateValues();
   
    public function __construct( string $lesson, $languageCodeHL1)
    {   
        $this->language1 = new LanguageModel();
        $this->language1->findOneByLanguageCodeHL( $languageCodeHL1);
        $this->bibleBlock = '';
        $this->biblePassage1 = '';
        $this->lesson = $lesson;
        $this->fileName = $this->findFileName($lesson, $languageCodeHL1 );
        $this->title = $this->findTitle($lesson, $languageCodeHL1);
        $this->setTranslation($this->getTranslationSource());
        $this->studyReferenceInfo = $this->getStudyReferenceInfo($lesson);
        $this->bibleReferenceInfo =new BibleReferenceInfoModel();
        $this->bibleReferenceInfo->setFromEntry($this->studyReferenceInfo->getEntry());
        $this->testament = $this->bibleReferenceInfo->getTestament();
        $this->bible1 = $this->findBibleOne($languageCodeHL1, $this->testament);
        $this->setPassage($this->bibleReferenceInfo);
        $this->qrcode1 = $this->createQrCode( $this->biblePassage1->getPassageUrl(), $languageCodeHL1);
    }
    protected function setTranslation ($source = 'dbs') {
        $translation1 = new TranslationModel($this->language1->getLanguageCodeHL(), $source);
        $this->translation1 = $translation1->getTranslationFile();
    }
    public function getFileName(){
        return $this->fileName;
    }
    public function getTranslation1(){
        return $this->translation1;
    }
    public function getTemplate(){
        return $this->template;
    }
    
    protected function findBibleOne($languageCodeHL1, $testament='NT')
    {
        $bible = new BibleModel($bibleRepository);
        $bible->setBestDbsBibleByLanguageCodeHL($languageCodeHL1, $testament);
        return $bible;
    }
   
    public function setPassage(BibleReferenceInfoModel $bibleReferenceInfo)
    {
        $this->bibleReferenceInfo = $bibleReferenceInfo;
        $this->biblePassage1= new PassageSelectController ($this->bibleReferenceInfo, $this->bible1);
    }
    private function fillPlaceHolder($placeholder, $value){
        $this->template = str_replace($placeholder, $value, $this->template);
    }
    private function fillPlaceHolderSpanLanguage1($placeholder, $value){
        $span = '<span dir="{{dir_language1}}" style="font-family:{{font_language1}};" >';
        $span .= $value . '</span>';
        $this->template = str_replace($placeholder, $span, $this->template);
    }
    public function setMonolingualTemplate($template = 'monolinguallDbs.template.html')
    {
        $file = ROOT_TEMPLATES . $template;
        if (!file_exists($file)){
            return null;
        }
        $this->template = file_get_contents($file);
        $this->createBibleBlock();
        $this->fillPlaceHolderSpanLanguage1('{{Bible Block}}', $this->bibleBlock);
        $this->fillPlaceHolderSpanLanguage1('{{language}}', $this->language1->getName());
        $this->fillPlaceHolder('{{Bible Reference}}', $this->biblePassage1->getReferenceLocalLanguage());
        $this->fillPlaceHolder('{{url}}', $this->biblePassage1->getPassageUrl());
        $this->fillPlaceHolder('{{QrCode1}}', $this->qrcode1);
        $this->fillPlaceHolderSpanLanguage1('{{Title}}', $this->title);
        $this->fillPlaceHolderSpanLanguage1('{{filename}}', $this->getFileName());
        $this->fillPlaceHolderSpanLanguage1('{{Video Block}}', '');
       
        foreach ($this->translation1 as $key => $value){
            $find= '{{' . $key . '}}';
            $this->fillPlaceHolderSpanLanguage1($find, $value);
        }
        $this->fillPlaceHolder('{{dir_language1}}', $this->language1->getDirection());
        $this->fillPlaceHolder('{{font_language1}}', $this->language1->getFont());
        $this->setUniqueTemplateValues();
        
    }
    private function createBibleBlock(){
        // a blank record is NULL
        if ($this->biblePassage1->getPassageText() !==  NULL){
            $this->bibleBlock = $this->biblePassage1->getPassageText();
        }
        else{
            $this->createBibleBlockWhenTextMissing();
        }
    }
    private function createBibleBlockWhenTextMissing(){
            $this->bibleBlock = $this->showTextOrLink($this->biblePassage1);
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
    public function saveMonolingualView(){
        $filePath= $this->getPathView() . $this->fileName .'.html';
        writeLogDebug('saveMonolingualView-176', $filePath);
        $text = $this->template;
        file_put_contents($filePath, $text);
    }
}