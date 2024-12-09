<?php

namespace App\Repositories;

abstract class BaseStudyRepository
{
    protected function expandPassageReferenceInfo(array $reference): array
    {
        $json = json_decode($reference['passage_reference_info'] ?? '', true);

        if (is_array($json)) {
            $reference['chapterStart'] = $json['chapterStart'] ?? null;
            $reference['chapterEnd'] = $json['chapterEnd'] ?? null;
            $reference['verseStart'] = $json['verseStart'] ?? null;
            $reference['verseEnd'] = $json['verseEnd'] ?? null;
            $reference['passageID'] = $json['passageID'] ?? null;
            $reference['uversionBookID'] = $json['uversionBookID'] ?? null;
        } else {
            // Handle invalid JSON gracefully
            $reference['chapterStart'] = null;
            $reference['chapterEnd'] = null;
            $reference['verseStart'] = null;
            $reference['verseEnd'] = null;
            $reference['passageID'] = null;
            $reference['uversionBookID'] = null;

            error_log('Failed to decode passage_reference_info: ' . ($reference['passage_reference_info'] ?? ''));
        }

        return $reference;
    }
}
