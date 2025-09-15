#!/usr/bin/env php
<?php
declare(strict_types=1);

/**
 * Local runner for the i18n translation queue.
 * Usage:
 *   php bin/run-translation-queue.php --seconds=20 --batch=50
 *   php bin/run-translation-queue.php --seconds=30 --fake
 */

use App\Cron\TranslationQueueProcessor;
use App\Services\Language\NullTranslationBatchService; // <-- fix import

require __DIR__ . '/../vendor/autoload.php';

// ---- parse args
$seconds = 30;
$batch   = 100;
$fake    = false;

foreach ($argv as $arg) {
    if (preg_match('/^--seconds=(\d+)$/', $arg, $m)) $seconds = (int)$m[1];
    if (preg_match('/^--batch=(\d+)$/',   $arg, $m)) $batch   = (int)$m[1];
    if ($arg === '--fake') $fake = true;
}

// ---- set env for local
putenv('APP_ENV=local');

// ---- spin up the processor
$proc = new TranslationQueueProcessor();

if ($fake) {
    $fakeTranslator = new NullTranslationBatchService(prefixMode: true);
    $ref  = new ReflectionObject($proc);
    $prop = $ref->getProperty('translator');
    $prop->setAccessible(true);
    $prop->setValue($proc, $fakeTranslator);
}

// ---- run once for N seconds (same logic as cron)
$proc->runCron($seconds, $batch);

echo "Done.\n";
