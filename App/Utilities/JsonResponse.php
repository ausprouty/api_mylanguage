<?php

namespace App\Utilities;

class JsonResponse {
    public static function success(array $data): void {
        header('Content-Type: application/json');
        $output =  json_encode(['status' => 'success', 'data' => $data]);
        echo ($output);
        exit;
    }

    public static function error(string $message, int $statusCode = 400): void {
        header('Content-Type: application/json', true, $statusCode);
        echo json_encode(['status' => 'error', 'message' => $message]);
        exit;
    }
}
