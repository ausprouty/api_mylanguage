<?php

use App\Models\Bible\BiblePassageModel;

trait BiblePassageTrait {
    /**
     * Creates a Bible passage ID from a Bible reference model.
     *
     * @param BiblePassageModel $passage The Bible reference model.
     * @return string The generated Bible passage ID.
     */
    public static function createPassageId(
        BiblePassageModel $passage
    ): string {
        return $passage->getBookID() . '-' .
            $passage->getChapterStart() . '-' .
            $passage->getVerseStart() . '-' .
            $passage->getVerseEnd();
    }
}
