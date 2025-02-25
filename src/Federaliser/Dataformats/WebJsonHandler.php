<?php
namespace Federaliser\Dataformats;

/**
 * Class WebJsonHandler
 * 
 * Fetches JSON data via HTTP GET from a URL, decodes it into an associative array,
 * normalises the array structure, and optionally filters the data by specified keys.
 * 
 * This class is useful for integrating remote JSON data sources accessible via HTTP or HTTPS.
 * It inherits common functionality from `AbstractHandler`, such as data normalization and filtering.
 * 
 * Security Notice:
 * - The URL specified in `source` is fetched using `file_get_contents()`.
 * - This can be a security risk if user input is directly used. Ensure that URLs are properly sanitised.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'type' => 'web-json',
 *     'source' => 'https://example.com/data.json',
 *     'query' => 'id,name,email'
 * ];
 * 
 * $handler = new WebJsonHandler($config);
 * $result = $handler->handle();
 * ```
 * 
 * Example Output:
 * ```
 * [
 *     ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
 *     ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com']
 * ]
 * ```
 * 
 * Design Considerations:
 * - This class promotes consistency in handling remote JSON data sources.
 * - It validates the URL before fetching to enhance security.
 * - It handles network errors and malformed JSON gracefully.
 * 
 * Security Recommendations:
 * - Always validate and sanitise user input if it influences the URL to prevent SSRF attacks.
 * - Consider using a whitelist of allowed domains for enhanced security.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser\Dataformats
 */
class WebJsonHandler extends AbstractHandler
{
    /**
     * Handles fetching the JSON data from a URL and processing it.
     * 
     * This method:
     * - Retrieves the URL from the `source` configuration.
     * - Validates the URL to ensure it is correctly formatted.
     * - Fetches the JSON data using `file_get_contents()` and captures the output.
     * - Decodes the JSON output into an associative array.
     * - Normalises the array structure.
     * - Filters the data based on query keys, if specified.
     * 
     * Example:
     * ```
     * $config = [
     *     'type' => 'web-json',
     *     'source' => 'https://example.com/data.json',
     *     'query' => 'id,name,email'
     * ];
     * 
     * $handler = new WebJsonHandler($config);
     * $result = $handler->handle();
     * ```
     * 
     * Error Handling:
     * - Throws `RuntimeException` if the URL is invalid or fetching fails.
     * - Throws `RuntimeException` if the JSON decoding fails.
     * - Returns an empty array if the JSON output is empty or malformed.
     * 
     * Security Note:
     * - The URL is validated using `filter_var()` to prevent SSRF attacks.
     * - Ensure that any dynamic parts of the URL (e.g., user input) are properly sanitised.
     * 
     * @return array Processed and optionally filtered data.
     * 
     * @throws \RuntimeException If the URL is invalid, fetching fails, or JSON decoding fails.
     */
    public function handle(): array
    {
        // Retrieve the URL from the configuration
        $url = $this->config['source'] ?? '';

        // Validate the URL to prevent SSRF attacks
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException("Invalid URL: $url");
        }

        // Fetch the JSON data from the URL
        $json = @file_get_contents($url);

        // Check if the URL fetch was successful
        if ($json === false) {
            throw new \RuntimeException("Unable to fetch URL: $url");
        }

        // Decode the JSON output into an associative array
        $data = json_decode($json, true);

        // Check for JSON decoding errors
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decode error: " . json_last_error_msg());
        }

        // Normalise the array structure
        $data = $this->normaliseArray($data);

        // Filter the data if query keys are specified
        return $this->filterData($data);
    }
}
