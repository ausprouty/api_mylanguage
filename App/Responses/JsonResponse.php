<?php
namespace App\Responses;

use App\Support\Trace;

final class JsonResponse
{
    /** Success: emits JSON and exits. */
    public static function success(
        $data = null,
        int $status = 200,
        array $headers = []
    ): never {
        self::out(['status' => 'ok', 'data' => $data], $status, $headers);
    }

    /** Error: emits JSON and exits. */
    public static function error(
        string $message,
        int $status = 500,
        array $headers = []
    ): never {
        self::out(['status' => 'error', 'message' => $message], $status, $headers);
    }

    /** Core emitter. */
    private static function out(
        array $payload,
        int $status,
        array $headers
    ): never {
        if (!isset($payload['meta']) || !is_array($payload['meta'])) {
            $payload['meta'] = [];
        }
        $payload['meta']['traceId'] = Trace::id();

        if (!headers_sent()) {
            http_response_code($status);
            header('Content-Type: application/json; charset=utf-8');
            header('X-Trace-Id: ' . Trace::id());
            foreach ($headers as $k => $v) {
                header($k . ': ' . $v, true);
            }
        }

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES
        );
        if ($json === false) {
            // last-ditch safe payload
            $json = '{"status":"error","message":"Encoding failed","meta":{"traceId":"'
                . addslashes(Trace::id()) . '"}}';
        }

        echo $json;
        exit;
    }
}
