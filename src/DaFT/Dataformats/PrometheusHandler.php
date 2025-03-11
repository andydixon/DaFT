<?php
namespace DaFT\Dataformats;

/**
 * Class PrometheusHandler
 * 
 * Executes queries against a Prometheus server via a cURL call.
 * - This class is designed to query a Prometheus server's HTTP API and retrieve metrics.
 * - It supports customizable queries and handles JSON responses.
 * - It inherits common functionality from `AbstractHandler`, such as data normalization and filtering.
 * 
 * Security Notice:
 * - Ensure that the Prometheus endpoint is secured to prevent unauthorised access.
 * - This class does not handle authentication; if required, extend the functionality accordingly.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'type' => 'prometheus',
 *     'source' => 'http://localhost',
 *     'port' => 9090,
 *     'query' => 'up'
 * ];
 * 
 * $handler = new PrometheusHandler($config);
 * $result = $handler->handle();
 * ```
 * 
 * Example Output:
 * ```
 * [
 *     'status' => 'success',
 *     'data' => [
 *         'resultType' => 'vector',
 *         'result' => [
 *             [
 *                 'metric' => ['__name__' => 'up', 'instance' => 'localhost:9090', 'job' => 'prometheus'],
 *                 'value' => [1632345678.123, '1']
 *             ]
 *         ]
 *     ]
 * ]
 * ```
 * 
 * Design Considerations:
 * - This class promotes flexible and efficient querying of Prometheus metrics.
 * - It handles network errors and JSON decoding errors gracefully.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Dataformats
 */
class PrometheusHandler extends AbstractHandler
{
    /**
     * Handles executing the configured query against a Prometheus server.
     * 
     * This method:
     * - Constructs the Prometheus query URL based on the configuration.
     * - Uses cURL to send the HTTP request to the Prometheus server.
     * - Checks for cURL errors and handles them gracefully.
     * - Decodes the JSON response and returns it as an associative array.
     * - Normalises the array structure and optionally filters the data by query keys.
     * 
     * Example:
     * ```
     * $config = [
     *     'type' => 'prometheus',
     *     'source' => 'http://localhost',
     *     'port' => 9090,
     *     'query' => 'up'
     * ];
     * 
     * $handler = new PrometheusHandler($config);
     * $result = $handler->handle();
     * ```
     * 
     * Error Handling:
     * - Returns an error array if the cURL request fails.
     * - Returns an error array if the Prometheus API returns a non-200 HTTP status.
     * - Returns an error array if the JSON response is invalid.
     * 
     * Security Note:
     * - Ensure that the Prometheus endpoint is secured to prevent unauthorised access.
     * - This class does not handle authentication; if required, extend the functionality accordingly.
     * 
     * @return array Query results as an associative array or error details.
     */
    public function handle(): array
    {
        // Retrieve connection details from configuration
        $host  = rtrim($this->config['source'] ?? '', '/');
        $port  = $this->config['port'] ?? 9090;
        $query = $this->config['query'] ?? 'up';

        // Validate the Prometheus host URL
        if (empty($host) || !filter_var($host, FILTER_VALIDATE_URL)) {
            return ['error' => 'Invalid Prometheus host URL', 'query' => $query];
        }

        // Construct the Prometheus query URL
        $url = "{$host}:{$port}/api/v1/query?query=" . urlencode($query);

        // Initialise cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10); // Set timeout for better error handling
        
        // Execute the cURL request
        $response = curl_exec($ch);

        // Check for cURL errors
        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error, 'query' => $query];
        }

        // Get the HTTP status code
        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        // Check for non-200 HTTP status codes
        if ($statusCode !== 200) {
            return ['error' => "Prometheus query failed with HTTP status: $statusCode", 'query' => $query];
        }

        // Decode the JSON response
        $json = json_decode($response, true);

        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error' => 'Invalid JSON response from Prometheus',
                'query' => $query,
                'json_error' => json_last_error_msg()
            ];
        }

        // Normalise the array structure
        $json = $this->normaliseArray($json);

        // Filter the data if query keys are specified
        return $this->filterData($json);
    }
}
