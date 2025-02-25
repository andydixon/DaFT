<?php
/**
 * ErrorHandler Class
 * 
 * This class provides centralised error handling for the Federaliser application.
 * It captures PHP errors and outputs them as structured JSON responses with relevant HTTP headers.
 * 
 * It is designed to be registered as a custom error handler using:
 * ```
 * set_error_handler([Federaliser\ErrorHandler::class, 'handleError']);
 * ```
 * 
 * Example Usage:
 * ```
 * // Register the custom error handler
 * set_error_handler([Federaliser\ErrorHandler::class, 'handleError']);
 * 
 * // Trigger an error for demonstration
 * echo $undefinedVariable;
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser
 */

namespace Federaliser;

class ErrorHandler
{
    /**
     * Handles PHP errors and outputs a structured JSON response.
     * 
     * This method is designed to be used as a custom error handler and:
     * - Captures PHP errors, warnings, and notices.
     * - Constructs a JSON response containing error details.
     * - Sets HTTP status to `500 Internal Server Error`.
     * - Outputs the response as `application/json`.
     * - Terminates script execution.
     * 
     * Error Format:
     * ```
     * {
     *     "status": "CORE_ERROR",
     *     "message": "Error message",
     *     "debug": {
     *         "file": "path/to/file.php",
     *         "line": 123
     *     }
     * }
     * ```
     * 
     * @param int    $errno   Error number (e.g., E_WARNING, E_NOTICE, E_ERROR).
     * @param string $errstr  Error message as a string.
     * @param string $errfile File name in which the error occurred.
     * @param int    $errline Line number at which the error occurred.
     * 
     * @return void
     */
    public static function handleError($errno, $errstr, $errfile, $errline): void
    {
        // Construct the error response as an associative array
        $response = [
            "status" => "CORE_ERROR",
            "message" => $errstr,
            "debug" => [
                "file" => $errfile,
                "line" => $errline
            ]
        ];

        // Set HTTP headers for JSON response and error status
        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');

        // Encode the response as JSON
        $jsonOutput = json_encode($response);

        // Check for JSON encoding errors
        if ($jsonOutput === false) {
            // If encoding fails, return a simplified error message
            $jsonOutput = json_encode([
                "status" => "CORE_ERROR",
                "message" => "JSON encoding failed for error response"
            ]);
        }

        // Output the JSON-encoded error response and terminate script execution
        echo $jsonOutput;
        exit;
    }
}
