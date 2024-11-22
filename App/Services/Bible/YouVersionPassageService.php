<?php

namespace App\Services\Bible;

use App\Models\Bible\BibleModel;
use App\Models\Bible\BibleReferenceModel;
use App\Services\Database\DatabaseService;

/**
 * Service for interacting with YouVersion Bible passages.
 * Handles URL construction, text retrieval, and reference localization.
 */
class YouVersionPassageService
{
    /**
     * @var DatabaseService Handles database interactions.
     */
    private $databaseService;

    /**
     * @var BibleReferenceModel Provides details about the Bible passage reference.
     */
    private $bibleReference;

    /**
     * @var BibleModel Represents Bible-specific metadata and configurations.
     */
    private $bible;

    /**
     * Constructor for YouVersionPassageService.
     *
     * @param DatabaseService $databaseService Service for database queries.
     * @param BibleReferenceModel $bibleReference Provides Bible reference data.
     * @param BibleModel $bible Represents Bible-specific metadata.
     */
    public function __construct(
        DatabaseService $databaseService,
        BibleReferenceModel $bibleReference,
        BibleModel $bible
    ) {
        $this->databaseService = $databaseService;
        $this->bibleReference = $bibleReference;
        $this->bible = $bible;
    }

    /**
 * Generates the URL for the requested Bible passage on YouVersion.
 *
 * The external ID is a string that may contain a placeholder `%`. For example:
 * `71/%.hau`. This placeholder is replaced with `$bibleBookAndChapter`, which
 * is dynamically constructed using the book ID, chapter, and verse details.
 * 
 * The generated URL is structured as:
 * `https://www.bible.com/bible/{formattedExternalId}`
 *
 * @return string The fully constructed passage URL.
 */
public function getPassageUrl(): string
{
    // Retrieve the book ID for YouVersion
    $uversionBibleBookID = $this->bibleReference->getUversionBookID();

    // Construct the Bible book, chapter, and verse details
    $bibleBookAndChapter = "{$uversionBibleBookID}." .
        "{$this->bibleReference->getChapterStart()}." .
        "{$this->bibleReference->getVerseStart()}-" .
        "{$this->bibleReference->getVerseEnd()}";

    // Replace the `%` placeholder in the external ID with the constructed value
    $formatted = str_replace('%', $bibleBookAndChapter, $this->bible->getExternalId());

    // Construct the final YouVersion passage URL
    $output = 'https://www.bible.com/bible/' . $formatted;

    // Return the final URL
    return $output;
}


    /**
     * Retrieves the full text of the Bible passage from an external source.
     *
     * @return string The full text of the passage.
     */
    public function getPassageText(): string
    {
        $url = $this->getPassageUrl();
        $webpageContent = $this->fetchExternalContent($url);

        // Logic to extract passage text from the webpage content goes here.
        // For example: parse the content and return the relevant section.

        return $this->extractPassageText($webpageContent);
    }

    /**
     * Retrieves the localized reference for the Bible passage.
     *
     * @return string The localized reference (e.g., book name and verse range).
     */
    public function getReferenceLocalLanguage(): string
    {
        $bookName = $this->getBookName();
        $chapterAndVerse = "{$this->bibleReference->getChapterStart()}:" .
            "{$this->bibleReference->getVerseStart()}-" .
            "{$this->bibleReference->getVerseEnd()}";

        return "{$bookName} {$chapterAndVerse}";
    }

    /**
     * Fetches external content from the specified URL.
     *
     * @param string $url The URL to fetch content from.
     * @return string The raw content retrieved from the URL.
     */
    private function fetchExternalContent(string $url): string
    {
        // Implementation to fetch external content (e.g., using cURL or a service).
        // For example:
        // $response = file_get_contents($url);
        // return $response;

        return "Fetched content from {$url}"; // Placeholder
    }

    /**
     * Extracts the passage text from the fetched webpage content.
     *
     * @param string $webpageContent The full webpage content.
     * @return string The extracted passage text.
     */
    private function extractPassageText(string $webpageContent): string
    {
        // Add logic to parse and extract passage text from the webpage content.
        // Placeholder implementation:
        return "Extracted passage text from content: {$webpageContent}";
    }

    /**
     * Retrieves the name of the book in the local language.
     *
     * @return string The book name in the localized language.
     */
    private function getBookName(): string
    {
        $query = "SELECT name FROM bible_book_names 
                  WHERE languageCodeHL = :languageCodeHL AND bookID = :bookID 
                  LIMIT 1";
        $params = [
            ':languageCodeHL' => $this->bibleReference->getLanguageCodeHL(),
            ':bookID' => $this->bibleReference->getBookID(),
        ];

        $result = $this->databaseService->executeQuery($query, $params);
        $bookName = $result->fetchColumn();

        if (!$bookName) {
            $bookName = $this->fetchAndSaveBookName();
        }

        return $bookName;
    }

    /**
     * Fetches the book name from an external source and saves it to the database.
     *
     * @return string The fetched and saved book name.
     */
    private function fetchAndSaveBookName(): string
    {
        // Placeholder for fetching book name from an external source.
        $bookName = "Fetched Book Name"; // Replace with real implementation.

        // Save the book name to the database.
        $query = "INSERT INTO bible_book_names (bookId, languageCodeHL, name) 
                  VALUES (:bookId, :languageCodeHL, :name)";
        $params = [
            ':bookId' => $this->bibleReference->getBookID(),
            ':languageCodeHL' => $this->bibleReference->getLanguageCodeHL(),
            ':name' => $bookName,
        ];
        $this->databaseService->executeQuery($query, $params);

        return $bookName;
    }
}
