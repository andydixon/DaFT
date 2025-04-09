<?php
namespace DaFT\Dataformats;

/**
 * Class InfluxDBHandler                   ***UNTESTED***
 * 
 * Executes queries against an InfluxDB server via an HTTP API call.
 * - This class is designed to query an InfluxDB server's HTTP API and retrieve time series data.
 * - It supports customizable queries and handles JSON responses.
 * - It inherits common functionality from `GenericHandler`, such as data normalization and filtering.
 * 
 * Security Notice:
 * - Ensure that the InfluxDB endpoint is secured to prevent unauthorized access.
 * - This class does not handle authentication by default; extend the functionality accordingly if required.
 * 
 * Usage Example:
 * ```
 * type   = influx
 * source = http://localhost
 * port   = 8086
 * db     = mydatabase
 * query  = SELECT * FROM measurement LIMIT 1
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-04-09
 * @namespace DaFT\Dataformats
 */
class InfluxHandler extends GenericHandler
{
    /**
     * Handles executing the configured query against an InfluxDB server.
     * 
     * Error Handling:
     * - Returns an error array if the cURL request fails.
     * - Returns an error array if the InfluxDB API returns a non-200 HTTP status.
     * - Returns an error array if the JSON response is invalid.
     * 
     * Security Note:
     * - Ensure that the InfluxDB endpoint is secured to prevent unauthorized access.
     * - This class does not handle authentication by default; extend the functionality accordingly if required.
     * 
     * @return array Query results as an associative array or error details.
     */
    public function handle(): array
    {
        // Retrieve connection details from configuration
        $host  = rtrim($this->config['source'] ?? '', '/');
        $port  = $this->config['port'] ?? 8086;
        $db    = $this->config['db'] ?? '';
        $query = $this->config['query'] ?? '';

        // Validate the InfluxDB host URL
        if (empty($host) || !filter_var($host, FILTER_VALIDATE_URL)) {
            return ['error' => 'Invalid InfluxDB host URL', 'query' => $query];
        }
        
        // Validate that a database and query have been provided
        if (empty($db)) {
            return ['error' => 'No database specified in configuration', 'query' => $query];
        }
        if (empty($query)) {
            return ['error' => 'No query specified in configuration', 'query' => $query];
        }
        
        // Construct the InfluxDB query URL
        $url = "{$host}:{$port}/query?db=" . urlencode($db) . "&q=" . urlencode($query);

        // Initialize cURL
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
            return ['error' => "InfluxDB query failed with HTTP status: $statusCode", 'query' => $query];
        }

        // Decode the JSON response
        $json = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return [
                'error'      => 'Invalid JSON response from InfluxDB',
                'query'      => $query,
                'json_error' => json_last_error_msg()
            ];
        }
        
        // Format the InfluxDB results by combining columns with their corresponding data rows
        $data = $this->formatResults($json);

        // Optionally filter the data if query keys are specified
        return $this->filterData($data);
    }

    /**
     * Formats the InfluxDB results by merging the "columns" and "values" arrays.
     * 
     * Each series in the results is processed so that each row in the "values"
     * array becomes an associative array where each key corresponds to the column
     * name.
     * 
     * @param array $json The original JSON response decoded into an array.
     * @return array The array with formatted series data.
     */
    private function formatResults(array $json): array
    {
        $formattedRows = [];
        if (isset($json['results'])) {
            foreach ($json['results'] as &$result) {
                if (isset($result['series']) && is_array($result['series'])) {
                    foreach ($result['series'] as &$series) {
                        // Only format if both 'columns' and 'values' are available
                        if (isset($series['columns'], $series['values']) && 
                            is_array($series['columns']) &&
                            is_array($series['values'])) {
                                $columns = $series['columns'];
                                foreach ($series['values'] as $row) {
                                    // Ensure the row has the same number of elements as the columns
                                    if (count($columns) === count($row)) {
                                        $formattedRows[] = array_combine($columns, $row);
                                    } else {
                                        // Optionally handle mismatched columns/row length if needed.
                                        $formattedRows[] = $row;
                                    }
                                }
                        }
                    }
                }
            }
        }
        return $formattedRows;
    }
}
