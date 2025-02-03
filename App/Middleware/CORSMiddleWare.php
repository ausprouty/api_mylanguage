<?php

namespace App\Middleware;
use App\Configuration\Config;
use App\Services\LoggerService;

/**
 * CORSMiddleware
 * 
 * This middleware handles Cross-Origin Resource Sharing (CORS) requests by setting appropriate headers
 * to control which origins are allowed to access resources. It checks the `Origin` header in the request
 * and, if the origin is allowed, sets the necessary CORS headers.
 * 
 * - **Allowed Origins**: The list of allowed origins is fetched from the environment configuration (`accepted_origins`).
 * - **CORS Headers**: If the origin is allowed, it sets the headers to allow specific HTTP methods and headers.
 * - **Logging**: Logs are written to track allowed and denied origins.
 * 
 * @param $request  The incoming request object.
 * @param $next     The next middleware or application logic in the chain.
 * 
 * @return mixed The result of the next middleware or application logic.
 */
class CORSMiddleware
{
    /**
     * Handle an incoming request and apply CORS headers if necessary.
     * 
     * This method checks the request for an `Origin` header and verifies whether the origin is in the list
     * of accepted origins (as defined by `accepted_origins` in the environment configuration). If the origin
     * is allowed, it sets the necessary CORS headers (`Access-Control-Allow-Origin`, `Access-Control-Allow-Methods`,
     * `Access-Control-Allow-Headers`, and `Access-Control-Allow-Credentials`). It logs the allowed and denied
     * origins for tracking purposes.
     * 
     * If the request does not have an `Origin` header or the origin is not allowed, an error is logged.
     * 
     * @param object $request The incoming HTTP request object.
     * @param callable $next  The next middleware or application logic in the chain.
     * 
     * @return mixed The result of the next middleware or application logic.
     */
    public function handle($request, $next)
    {
        // Fetch accepted origins from the environment configuration
        $acceptedOrigins = Config::get('accepted_origins');
        $origin = $_SERVER['HTTP_ORIGIN'] ?? '';
        LoggerService::logInfo('CORSMiddleware-46',   $origin);
        // Check if the request contains an Origin header
        if ($origin && in_array($origin, $acceptedOrigins)) { 
            // Set the CORS headers for allowed origins
            header('Access-Control-Allow-Origin: ' . $origin);
            header("Access-Control-Allow-Methods: GET, POST, PUT, DELETE, OPTIONS");
            header("Access-Control-Allow-Headers: Content-Type, Authorization");
            header("Access-Control-Allow-Credentials: true");
            if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
                header("HTTP/1.1 204 No Content");
                exit();
            }
    
        } else {
            // Log the absence of an Origin header (non-CORS requests)
            LoggerService::logError('CORSMiddleware-61', 'No Origin header present in the request.');
        }
        // Proceed to the next middleware or application logic
        return $next($request);
    }
}
