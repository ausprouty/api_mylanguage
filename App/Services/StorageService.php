<?php
namespace App\Services;

use App\Configuration\Config;

/**
 * Service for managing the storage and retrieval of study content files.
 * Files are stored in a subdirectory of the ROOT_RESOURCES directory.
 */
class StorageService {
    /**
     * @var string $storagePath The absolute path to the storage directory.
     */
    private $storagePath;

    /**
     * Constructor for the StorageService.
     *
     * @param string $storagePath The relative path within ROOT_RESOURCES for storing files.
     */
    public function __construct(string $storagePath) {
        // Combine ROOT_RESOURCES with the provided storage subdirectory
        $this->storagePath = Config::get('ROOT_RESOURCES') . $storagePath;
    }

    /**
     * Retrieves a stored file's content by its key.
     *
     * @param string $key The unique key (filename) identifying the stored file.
     * @return string|null The content of the file if it exists, or null if the file is not found.
     */
    public function retrieve(string $key): ?string
{
    // Normalize the file path to prevent directory traversal attacks
    $filePath = realpath($this->storagePath . '/' . $key);

    // Check if the file exists within the allowed storage path
    if ($filePath === false || strpos($filePath, $this->storagePath) !== 0 || !file_exists($filePath)) {
        return null; // File does not exist or is outside the storage path
    }

    // Return the file content, or null on failure
    return file_get_contents($filePath) ?: null;
}

    /**
     * Stores content in a file identified by a unique key.
     *
     * @param string $key The unique key (filename) for storing the file.
     * @param string $content The content to be written to the file.
     * @return void
     */
    public function store(string $key, string $content): void {
        // Construct the full file path
        $filePath = $this->storagePath . '/' . $key;

        // Write the content to the file
        file_put_contents($filePath, $content);
    }
}
