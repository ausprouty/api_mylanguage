<?php

require __DIR__ . '/../../vendor/autoload.php';

use App\Configuration\Config;
use App\Factories\BibleStudyReferenceFactory;

// Bootstrap app config and container
Config::initialize();
$container = require __DIR__ . '/../Configuration/container.php';

/** @var BibleStudyReferenceFactory $factory */
$factory = $container->get(BibleStudyReferenceFactory::class);

$studies = [
    'share' => 9,
    'trust' => 17,
    'obey' => 8,
    'relate' => 9,
    'serve' => 9,
    'give' => 9,
    'disciple' => 8,
];

foreach ($studies as $study => $maxLesson) {
    for ($lesson = 1; $lesson <= $maxLesson; $lesson++) {
        try {
            $model = $factory->createModel($study, $lesson);
            echo "✅ Populated $study lesson $lesson\n";
        } catch (Throwable $e) {
            echo "⚠️  Skipped $study lesson $lesson: " . $e->getMessage() . "\n";
        }
    }
}
