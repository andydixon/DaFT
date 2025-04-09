<?php
namespace DaFT\Dataformats;

/**
 * Class SplunkHandler              ***UNTESTED*** 
 *
 * Executes searches against a Splunk server via its HTTP API.
 * - uses the oneshot search endpoint to run a search query and retrieve results in JSON format.
 *
 * Usage Example:
 * ```
 * config:
 *     type     = splunk
 *     source   = https://splunk.mycompany.com
 *     port     = 8089
 *     query    = search index=main error
 *     username = admin
 *     password = changeme
 **
 * @author Andy Dixon
 * @created 2025-04-09
 * @namespace DaFT\Dataformats
 */
class SplunkHandler extends GenericHandler
{
    /**
     * Handles executing the configured search query against a Splunk server.
     * Error Handling:
     * - Returns an error array if the cURL request fails.
     * - Returns an error array if the Splunk API returns a non-200 HTTP status.
     * - Returns an error array if the JSON response is invalid.
     *
     * Security Note:
     * - Ensure that the Splunk endpoint is secured to prevent unauthorized access.
     * - This class uses basic HTTP authentication by default; extend the functionality if needed.
     *
     * @return array Search results as an associative array or error details.
     */
    public function handle(): array
    {
        // Retrieve connection details from configuration
        $host     = rtrim($this->config['source'] ?? '', '/');
        $port     = $this->config['port'] ?? 8089;
        $query    = $this->config['query'] ?? '';
        $username = $this->config['username'] ?? '';
        $password = $this->config['password'] ?? '';

        // Validate the Splunk host URL
        if (empty($host) || !filter_var($host, FILTER_VALIDATE_URL)) {
            return ['error' => 'Invalid Splunk host URL', 'query' => $query];
        }
        
        // Ensure that a query is provided
        if (empty($query)) {
            return ['error' => 'No query specified in configuration', 'query' => $query];
        }
        
        // Construct the Splunk oneshot search URL.
        // Typically, the oneshot search endpoint is used to perform a single search and export the results.
        $url = "{$host}:{$port}/services/search/jobs/export";

        // Prepare POST parameters. Splunk expects a "search" parameter
        // If needed, ensure the query starts with "search " as per Splunk syntax.
        $postFields = http_build_query([
            'search'      => $query,
            'output_mode' => 'json'
        ]);

        // Initialize cURL
        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30); // Increase timeout if necessary
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $postFields);

        // Set HTTP Basic Authentication if credentials are provided
        if (!empty($username) && !empty($password)) {
            curl_setopt($ch, CURLOPT_USERPWD, "{$username}:{$password}");
        }

        // Optionally, for HTTPS connections disable SSL verification.
        // For production, you should handle certificates securely.
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);

        // Execute the cURL request
        $response = curl_exec($ch);
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
            return ['error' => "Splunk query failed with HTTP status: $statusCode", 'query' => $query];
        }

        // Decode the JSON response
        $json = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error'      => 'Invalid JSON response from Splunk',
                'query'      => $query,
                'json_error' => json_last_error_msg()
            ];
        }

        // Normalize the array structure using inherited functionality
        $json = $this->normaliseArray($json['results']);

        // Optionally, further processing can be added here
        // For example, if the results need additional formatting

        // Filter the data if query keys are specified
        return $this->filterData($json);
    }
}
