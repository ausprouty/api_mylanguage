<?php

/**
 * Import translation data and save as JSON in the languages folder.
 */
function importTranslations()
{

    // Resolve the directory part only
    $directory = realpath(__DIR__ . '/../data/translationImports/');
    $file = 'LeadershipTranslations.txt';

    // Check if the directory is valid
    if ($directory === false) {
        displayErrorMessage("The directory should exist at: " . str_replace('/', DIRECTORY_SEPARATOR, __DIR__ . '/../data/translationImports/'));
        return;
    }

    // Append the file name to the resolved directory
    $cleanPath = $directory . DIRECTORY_SEPARATOR . basename($file);

    // Check if the file exists
    if (!file_exists($cleanPath)) {
        displayErrorMessage("The file should exist at: $cleanPath");
        return;
    }

    $text = file_get_contents($cleanPath);
    $lines = explode("\n", $text);
    $twig = [];

    foreach ($lines as $line) {
       processTranslationLine($line, $twig);
    }
}

/**
 * Display an error message if the source file is not found.
 */
function displayErrorMessage($error_message)
{
    echo "This is an error message: " . $error_message;
    echo '<p>  Instructions for creating and uploading the source file:</p>';
    echo '<ol>
            <li>Login to Chrome using bob.prouty@powertochange.org.au</li>
            <li>Prepare the data in the <a href="https://docs.google.com/spreadsheets/d/16cQMIo-DXD58At6IvNPWxEimbF3cFoW_EZNrLsdaluo/edit#gid=0"">Google Spreadsheet</a>.</li>
            <li>Copy and paste into Excel, transpose, and save as UTF-8 encoded text.</li>
            <li>To transpose: 
            <ul><li> Select the data you want to transpose, including any row or column headers.</li>
<li>Press Ctrl+C (or Cmd+C on Mac) to copy the data.</li>
<li>Open a new tab,table or worksheet.</li>
<li>Right-click on the destination cell in the upper left had corner.</li>
<li>Select Paste Special > Transpose:</li>

    <li>Right-click and choose the Transpose icon in the Paste Options.</li>
    </ul></li>
    <li>Open Notepad++ and make sure the encoding is UTF-8</li>
    <li>Copy from Excel into Notepad++ and make sure letters are formed correctly</li>
            <li>Place the file in `data/translationImports/LeadershipTranslations.txt`.</li>
          </ol>';
    echo '<p>Ensure the first column is Twig Key.</p>';
}

/**
 * Process a single line of the translation file.
 *
 * @param string $line The line from the file.
 * @param array $twig Array to store Twig keys for translations.
 */
function processTranslationLine($line, &$twig)
{
    $translation = [];
    $items = explode("\t", $line);
    if (!isset($items[1])){
        die;
    }
    $hl_id = strtolower($items[1]);

    echo "$hl_id <br>";

    for ($i = 3; $i < 100; $i++) {
        if (array_key_exists($i, $items)) {
            $words = $items[$i];
            if ($words) {
                if ($hl_id === 'twig') {
                    $twig[$i] = $words;
                }
                $key = $twig[$i];
                $translation[$key] = $words;
            }
        }
    }

    saveTranslation($hl_id, $translation);
}

/**
 * Save the translation as a JSON file in the appropriate language directory.
 *
 * @param string $hl_id The language ID.
 * @param array $translation The translation data.
 */
function saveTranslation($hl_id, $translation)
{
    $directory = __DIR__ . '/../Resources/translations/languages/' . $hl_id;
    if (!file_exists($directory)) {
        mkdir($directory, 0755, true);
    }

    $filename = $directory . '/leadership.json';
    $json = json_encode($translation, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);

    file_put_contents($filename, $json);
}
if (php_sapi_name() === 'cli') {
    // Running from the command line
    echo "Running from the command line.\n";
} else {
    // Running from a web server
    $allowed_ips = ['127.0.0.1', '::1']; // Localhost IPs
    if (!isset($_SERVER['REMOTE_ADDR']) || !in_array($_SERVER['REMOTE_ADDR'], $allowed_ips)) {
        http_response_code(403);
        echo "Access denied.";
        exit;
    }
}

// Run the import script
importTranslations();
