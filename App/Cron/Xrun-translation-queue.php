<?php

require_once __DIR__ . '/../../vendor/autoload.php';  // lowercase "vendor" is correct

use App\Cron\TranslationQueueProcessor;
use App\Services\ThrottleService;
use App\Configuration\Config;


$throttle = new ThrottleService('translation_queue');

if ($throttle->tooSoon(200)) {
    echo json_encode(['status' => 'skip - too soon']);
    exit;
}
// Only require token if run from web server
if (php_sapi_name() !== 'cli') {
    if ($_GET['token'] ?? '' !== Config::get('api.cron_secret')) {
        http_response_code(403);
        exit('Unauthorized');
    }
}

$processor = new TranslationQueueProcessor();
$processor->run();
$throttle->updateTimestamp();
echo json_encode(['status' => 'processed']);
