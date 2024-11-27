<?php
namespace App\Services\BiblePassage;

use App\Models\Bible\BibleModel;
use App\Models\Bible\PassageReferenceModel;

abstract class AbstractBiblePassageService
{
    protected $passageReference;
    protected $bible;

    public function __construct(PassageReferenceModel $passageReference, BibleModel $bible)
    {
        $this->passageReference = $passageReference;
        $this->bible = $bible;
    }

    // Force subclasses to implement these methods
    abstract public function getPassageText(): string;
    abstract public function getPassageUrl(): string;
    abstract public function getReferenceLocalLanguage(): string;
}
