<?php

namespace App\Responses;

use App\Responses\ResponseBuilder;


class JsonResponse
{
    /**
     * Output a success response using ResponseBuilder.
     *
     * @param array|object $data
     */
    public static function success(array|object $data): void
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        ResponseBuilder::ok()
            ->withData($data)
            ->json(); // outputs and exits
    }

    /**
     * Output an error response using ResponseBuilder.
     *
     * @param string $message
     * @param int $statusCode (optional)
     */
    public static function error(string $message, int $statusCode = 400): void
    {
        ResponseBuilder::error($message)
            ->withMeta(['http_status' => $statusCode]) // optional: communicate it
            ->json($statusCode); // outputs and exits
    }
}
