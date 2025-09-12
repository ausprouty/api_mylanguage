<?php
namespace App\Responses;

use App\Support\Trace;

final class JsonResponse
{
    /**
     * Success: data + optional headers + optional status (defaults 200).
     * Back-compat: if the 2nd arg is int, it is treated as $status.
     */
    public static function success(
        $data = null,
        array|int|null $headersOrStatus = null,
        ?int $status = null
    ): void {
        $headers = [];
        if (is_int($headersOrStatus) && $status === null) {
            $status = $headersOrStatus;
        } elseif (is_array($headersOrStatus)) {
            $headers = $headersOrStatus;
        }
        $status = $status ?? 200;

        self::out(['status' => 'ok', 'data' => $data], $status, $headers);
    }

    /**
     * Error: message + optional status (default 500) + optional headers.
     */
    public static function error(
        string $message,
        int $status = 500,
        array $headers = []
    ): void {
        self::out(['status' => 'error', 'message' => $message], $status,
            $headers);
    }

    /**
     * Core emitter with headers support and trace id.
     */
    private static function out(
        array $payload,
        int $status,
        array $headers = []
    ): void {
        if (!isset($payload['meta']) || !is_array($payload['meta'])) {
            $payload['meta'] = [];
        }
        $payload['meta']['traceId'] = $payload['meta']['traceId']
            ?? Trace::id();

        http_response_code($status);

        // Default headers
        $default = [
            'Content-Type' => 'application/json; charset=utf-8',
            'X-Trace-Id'   => $payload['meta']['traceId'],
        ];

        // Merge (caller can override defaults if needed)
        foreach ($default as $k => $v) {
            if (!array_key_exists($k, $headers)) {
                $headers[$k] = $v;
            }
        }

        foreach ($headers as $k => $v) {
            header($k . ': ' . $v);
        }

        $json = json_encode(
            $payload,
            JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE
        );

        if ($json === false) {
            $fallback = [
                'status' => 'error',
                'message' => 'Encoding error',
                'meta' => ['traceId' => $payload['meta']['traceId']],
            ];
            echo json_encode($fallback);
            exit;
        }

        echo $json;
        exit;
    }
}
