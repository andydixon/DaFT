<?php
/**
 * JSON Exporter Class
 * 
 * This class is responsible for exporting data as a JSON response.
 * It sets the appropriate headers and handles JSON encoding.
 * If encoding fails, it returns a 500 Internal Server Error.
 * 
 * Usage Example:
 * ```
 * JsonExporter::export($data, 200, $config);
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Exporters
 */

namespace DaFT\Exporters;

class JsonExporter extends AbstractExporter
{
    /**
     * Exports data as a JSON response.
     * 
     * This method:
     * - Sets the `Content-Type` header to `application/json` with UTF-8 encoding.
     * - Outputs the data as a JSON-encoded string.
     * - Sets the HTTP status code based on the `$statusCode` parameter.
     * - Ends script execution after outputting the response.
     * 
     * It also checks for JSON encoding errors and sends a `500 Internal Server Error`
     * if encoding fails.
     * 
     * Example Usage:
     * ```
     * JsonExporter::export(['success' => true, 'data' => $payload], 200);
     * ```
     * 
     * @param array $data            The data to be exported as JSON.
     * @param int   $statusCode      The HTTP status code for the response (default: 200).
     * @param array $additionalConfig Additional configuration parameters (optional, not used in this implementation).
     * 
     * @return void
     */
    public static function export(array $data, int $statusCode = 200, array $additionalConfig = []): void
    {
        $headerHandler = self::$headerHandler ?? 'header';

        // Set Content-Type header to application/json with UTF-8 encoding
        $headerHandler('Content-Type: application/json; charset=utf-8', true, $statusCode);

        // Encode data as JSON with numeric check and UTF-8 support
        $jsonOutput = json_encode($data, JSON_NUMERIC_CHECK | JSON_UNESCAPED_UNICODE);

        // Check for JSON encoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            // If encoding fails, set HTTP status to 500 Internal Server Error
            http_response_code(500);

            // Output error message and terminate script execution
            echo json_encode([
                'error' => 'JSON encoding error',
                'message' => json_last_error_msg()
            ]);
        } else {

            // Output the JSON-encoded data and terminate script execution
            echo $jsonOutput;
        }
    }
}
