<?php
declare(strict_types=1);

namespace App\Responses;

class JsonResponse
{
    /**
     * @param array|object $data
     * @param array<string,string> $headers
     * @param int $statusCode
     */
    public static function success(
        array|object $data,
        array $headers = [],
        int $statusCode = 200
    ): void {
        if (is_object($data)) {
            $data = (array) $data;
        }

        ResponseBuilder::ok()
            ->withData($data)
            ->json($statusCode, $headers);
    }

    public static function error(
        string $message,
        int $statusCode = 400,
        array $headers = []
    ): void {
        ResponseBuilder::error($message)
            ->withMeta(['http_status' => $statusCode])
            ->json($statusCode, $headers);
    }

    /** 304 helper for ETag matches */
    public static function notModified(string $etag): void
    {
        http_response_code(304);
        header('ETag: "' . $etag . '"');
    }
}
