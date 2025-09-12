<?php

namespace App\Services\BiblePassage;

use App\Services\BiblePassage\AbstractBiblePassageService;
use App\Services\Web\BibleWordConnectionService;
use App\Services\LoggerService;
use App\Configuration\Config;

/**
 * BibleWordPassageService handles Bible passage retrieval from WordProject.
 * It decides between local file and web fetch, then extracts verses.
 */
class BibleWordPassageService extends AbstractBiblePassageService
{
    /** Build the public chapter URL. */
    public function getPassageUrl(): string
    {
        $url  = 'https://wordproject.org/bibles/';
        $url .= $this->bible->getExternalId();
        $url .= '/' . $this->formatChapterPage() . '.htm';
        return $url;
    }

    /**
     * Get the raw HTML, preferring local cache when present.
     * Returns ['<html>'] and sets $this->webpage for later use.
     */
    public function getWebPage(): array
    {
        $webpage = [];
        $local = $this->generateFilePath();
        LoggerService::logInfo('BibleWordPassageService-37', $local);

        if (is_file($local)) {
            LoggerService::logInfo(
                'BibleWordPassageService-43',
                $local . ' exists'
            );
            $webpage[0] = $this->fetchFromFileDirectory($local);
        } else {
            LoggerService::logInfo(
                'BibleWordPassageService-43',
                $local . ' does NOT exist'
            );
            $conn = $this->fetchFromWeb(); // BibleWordConnectionService
            $remote = $conn->response();   // ['code','body','ctype','final',...]
            if (($remote['code'] ?? 0) !== 200) {
                $msg = 'HTTP ' . ($remote['code'] ?? '??') .
                    ' from ' . ($remote['final'] ?? $this->getPassageUrl());
                LoggerService::logError('BibleWordPassageService-http', $msg);
                throw new \RuntimeException($msg);
            }
            $webpage[0] = (string) ($remote['body'] ?? '');
        }

        // Persist into instance for later helpers that read $this->webpage
        $this->webpage = $webpage;

        return $webpage;
    }

    /** Generate the absolute local file path for cached HTML. */
    private function generateFilePath(): string
    {
        $root = rtrim(Config::getDir('resources.root'), "/\\"); // .../Resources
        $lang = $this->bible->getExternalId(); // e.g. kn
        $rel  = 'bibles/wordproject/' .
            $lang . '/' . $this->formatChapterPage() . '.html';

        // Safe join
        $path = $root . DIRECTORY_SEPARATOR .
            str_replace('/', DIRECTORY_SEPARATOR, ltrim($rel, "/\\"));

        LoggerService::logInfo('BibleWordPassageService-59', $path);
        return $path;
    }

    /** Book/chapter path like "42/7". */
    private function formatChapterPage(): string
    {
        $book = (int) $this->passageReference->getBookNumber();
        $book = str_pad((string) $book, 2, '0', STR_PAD_LEFT);
        $chapter = (int) $this->passageReference->getChapterStart();
        return $book . '/' . $chapter;
    }

    /**
     * Fetch from WordProject.
     * @return BibleWordConnectionService
     */
    public function fetchFromWeb(): BibleWordConnectionService
    {
        $endpoint = $this->bible->getExternalId() . '/' .
            $this->formatChapterPage() . '.htm';

        $conn = new BibleWordConnectionService($endpoint);

        if (!$conn) {
            LoggerService::logError(
                'BibleWordPassageService-84',
                'Failed to fetch Bible passage from WordProject.'
            );
            throw new \RuntimeException('Remote fetch failed');
        }

        return $conn;
    }

    /** Read a local cached HTML file. */
    private function fetchFromFileDirectory(string $filename): string
    {
        LoggerService::logInfo(
            'BibleWordPassageService-102',
            'Fetching file from local source'
        );
        return (string) file_get_contents($filename);
    }

    /** Extract selected verses as HTML <p><sup>N</sup>text</p> blocks. */
    public function getPassageText(): string
    {
        $html = $this->webpage[0] ?? '';
        if ($html === '') {
            return '';
        }

        $chapter = $this->trimToChapter($html);
        $verses  = $this->trimToVerses($chapter);

        return $verses;
    }

    /** Slice the chapter content out of the page HTML. */
    private function trimToChapter(string $pageHtml): string
    {
        $startMarker = '<!--... the Word of God:-->';
        $endMarker   = '<!--... sharper than any twoedged sword... -->';

        $startPos = strpos($pageHtml, $startMarker);
        $endPos   = strpos($pageHtml, $endMarker);

        if ($startPos === false || $endPos === false || $endPos <= $startPos) {
            // Fallback: return full body if markers missing
            return $pageHtml;
        }

        $startPos += strlen($startMarker);
        return substr($pageHtml, $startPos, $endPos - $startPos);
    }

    /** Keep only the requested verse range and format lines. */
    private function trimToVerses(string $chapterHtml): string
    {
        LoggerService::logInfo('BibleWordPassageService-180', $chapterHtml);

        $selected = $this->selectVerses($chapterHtml);

        LoggerService::logInfo('BibleWordPassageService-182', $selected);

        if ($selected === '') {
            return '';
        }

        return "\n<!-- begin bible -->" .
               $selected .
               "\n<!-- end bible -->\n";
    }

    /** Build formatted HTML for verses inside the requested range. */
    private function selectVerses(string $page): string
    {
        // Normalize and remove container tags we don't want
        $page = str_replace(
            ['<!--span class="verse"', '<p>', '</p>', '<br/>', '<br />'],
            ['<span class="verse"',   '',    '',     '<br>',  '<br>'],
            $page
        );

        $vStart = (int) $this->passageReference->getVerseStart();
        $vEnd   = (int) $this->passageReference->getVerseEnd();
        $range  = range($vStart, $vEnd);

        $out = '';
        foreach (explode('<br>', $page) as $line) {
            $line = trim($line);
            if ($line === '') {
                continue;
            }
            $n = $this->extractVerseNumber($line);
            if ($n !== 0 && in_array($n, $range, true)) {
                $out .= $this->formatVerseLine($n, $line);
            }
        }
        return $out;
    }

    /** Format one verse as <p><sup>N</sup>text</p>. */
    private function formatVerseLine(int $verseNum, string $line): string
    {
        $lastSpan = strripos($line, '</span>');
        $verseText = $lastSpan !== false
            ? substr($line, $lastSpan + strlen('</span>'))
            : $line;

        return '<p><sup>' . $verseNum . '</sup>' .
               $verseText .
               '</p>' . "\n";
    }

    /** Extract trailing number from a verse line’s <span>…</span>. */
    private function extractVerseNumber(string $line): int
    {
        $end = strripos($line, '</span>');
        if ($end === false) {
            return 0;
        }
        $start = strrpos(substr($line, 0, $end), '>');
        if ($start === false) {
            return 0;
        }
        $num = trim(substr($line, $start + 1, $end - $start - 1));
        return (int) $num;
    }

    /**
     * Extract the local-language book title from <title> and append verse range.
     * Example: "Luka:10-12"
     */
    public function getReferenceLocalLanguage(): string
    {
        $html = $this->webpage[0] ?? '';
        if ($html === '') {
            return $this->passageReference->getVerseStart() . '-' .
                $this->passageReference->getVerseEnd();
        }

        preg_match('/<title>(.*?)<\/title>/i', $html, $m);
        $title = $m[1] ?? '';

        preg_match('/^([^\d]+)/u', $title, $m2);
        $book = isset($m2[1]) ? trim($m2[1]) : '';

        return $book . ':' .
            $this->passageReference->getVerseStart() . '-' .
            $this->passageReference->getVerseEnd();
    }
}
