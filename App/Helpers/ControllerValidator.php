<?php

namespace App\Helpers;

use App\Services\LoggerService;
use App\Responses\JsonResponse;

class ControllerValidator
{
    public static function validateArgs(array $args, array $required = [], array $optional = [], array $casts = []): ?array
    {
        // Check for missing required fields
        foreach ($required as $key) {
            if (!isset($args[$key])) {
                LoggerService::logInfo('MissingArgs', print_r($args, true));
                JsonResponse::error("Missing required argument: $key");
                return null;
            }
        }

        // Prepare merged and casted values
        $validated = [];

        // Set required values
        foreach ($required as $key) {
            $validated[$key] = self::applyCast($args[$key], $casts[$key] ?? null);
        }

        // Set optional values (default to null)
        foreach ($optional as $key) {
            $validated[$key] = isset($args[$key])
                ? self::applyCast($args[$key], $casts[$key] ?? null)
                : null;
        }

        return $validated;
    }

    private static function applyCast($value, ?string $cast)
    {
        return match ($cast) {
            'int' => (int) $value,
            'float' => (float) $value,
            'bool' => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            default => $value,
        };
    }
}
