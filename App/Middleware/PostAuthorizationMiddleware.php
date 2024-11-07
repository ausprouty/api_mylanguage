<?php

namespace App\Middleware;

use App\Services\Security\SanitizeInputService;
use App\Controllers\Data\PostInputController;
use App\Services\Security\AdminAuthorizationService;

/**
 * PostAuthorizationMiddleware
 * 
 * This middleware handles POST requests by sanitizing input data and ensuring that the request is authorized.
 * It processes incoming POST data, sanitizes it using the `SanitizeInputService`, and then checks for proper 
 * authorization headers before allowing the data to be processed further.
 *
 * - **Sanitize Input**: The input data is sanitized to prevent malicious input.
 * - **Authorization**: The request is verified using the `AdminAuthorizationService`. If the request is not authorized,
 *   a 401 Unauthorized response is returned.
 * - **Logging**: Logs are written to track the input data and the result of the authorization check.
 * 
 * @method static array getDataSet() Retrieves and processes the sanitized POST data if the request is authorized.
 * 
 * @return array|string If the request is authorized, the sanitized data set is returned. Otherwise, 'not authorized' is returned with a 401 response code.
 */
class PostAuthorizationMiddleware {

    /**
     * Process the POST request, sanitize the input data, and check for authorization.
     * 
     * This method checks if the incoming request is a POST request. If it is, it sanitizes the input data using
     * the `SanitizeInputService` and retrieves the sanitized data through the `PostInputController`. It also
     * verifies if the request is authorized by checking the authorization headers. If unauthorized, it returns
     * a 401 status and an 'not authorized' message. If authorized, it returns the sanitized data set.
     * 
     * @return array|string Returns the sanitized data set if authorized, otherwise 'not authorized'.
     */
    static public function getDataSet() {
    
        // Initialize the dataSet array
        $dataSet = [];

        // Check if the request method is POST
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Log the raw POST data
        
            
            // Sanitize the input data using SanitizeInputService
            $sanitizeInputService = new SanitizeInputService();
            $postInputController = new PostInputController($sanitizeInputService);

            // Retrieve and log the sanitized data
            writeLog('PostAuthorizationMiddleware-20', $postInputController->getDataSet());

            // Check if the request is from an authorized site
            $authorized = AdminAuthorizationService::checkAuthorizationHeader();
            if (!$authorized) {
                // Log unauthorized access and send 401 status
                error_log('not authorized based on authorization header');
                http_response_code(401);
                return 'not authorized based on authorization header';
            }

            // If authorized, retrieve the sanitized data set
            $dataSet = $postInputController->getDataSet();
        }

        // Return the sanitized data set
        return $dataSet;
    }
}
