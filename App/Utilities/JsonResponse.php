<?php

namespace App\Utilities;
use App\Services\LoggerService;

class JsonResponse {
    public static function success(array|object $data): void {
        // Convert object to array if needed
        if (is_object($data)) {
            $data = (array) $data;
        }

        header('Content-Type: application/json');
        // Encode JSON with pretty print and unescaped Unicode
        //$output = json_encode(
        //    ['status' => 'success', 'data' => $data],
        //    JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE
        //);
        $output = json_encode(['status' => 'success', 'data' => $data]);
        //LoggerService::logInfo('jsonResponse-20', $output);
        echo ($output);
        exit;
    }

    public static function error(string $message, int $statusCode = 400): void {
        header('Content-Type: application/json', true, $statusCode);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
}
