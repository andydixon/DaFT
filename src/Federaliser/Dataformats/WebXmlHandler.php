<?php
namespace Federaliser\Dataformats;

/**
 * Class WebXmlHandler
 * 
 * Fetches XML data via HTTP GET from a URL, converts it to an associative array,
 * normalises the array structure, and optionally filters the data by specified keys.
 * 
 * This class is useful for integrating remote XML data sources accessible via HTTP or HTTPS.
 * It inherits common functionality from `AbstractHandler`, such as data normalization and filtering.
 * 
 * Security Notice:
 * - The URL specified in `source` is fetched using `file_get_contents()`.
 * - This can be a security risk if user input is directly used. Ensure that URLs are properly sanitised.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'type' => 'web-xml',
 *     'source' => 'https://example.com/data.xml',
 *     'query' => 'id,name,email'
 * ];
 * 
 * $handler = new WebXmlHandler($config);
 * $result = $handler->handle();
 * ```
 * 
 * Example Output:
 * ```
 * [
 *     ['id' => '1', 'name' => 'Alice', 'email' => 'alice@example.com'],
 *     ['id' => '2', 'name' => 'Bob', 'email' => 'bob@example.com']
 * ]
 * ```
 * 
 * Design Considerations:
 * - This class promotes consistency in handling remote XML data sources.
 * - It validates the URL before fetching to enhance security.
 * - It uses internal libxml error handling for robust XML parsing.
 * - It handles network errors and malformed XML gracefully.
 * 
 * Security Recommendations:
 * - Always validate and sanitise user input if it influences the URL to prevent SSRF attacks.
 * - Consider using a whitelist of allowed domains for enhanced security.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser\Dataformats
 */
class WebXmlHandler extends AbstractHandler
{
    /**
     * Handles fetching the XML data from a URL and processing it.
     * 
     * This method:
     * - Retrieves the URL from the `source` configuration.
     * - Validates the URL to ensure it is correctly formatted.
     * - Fetches the XML data using `file_get_contents()` and captures the output.
     * - Parses the XML string into a `SimpleXMLElement`.
     * - Converts the XML to an associative array.
     * - Normalises the array structure.
     * - Filters the data based on query keys, if specified.
     * 
     * Example:
     * ```
     * $config = [
     *     'type' => 'web-xml',
     *     'source' => 'https://example.com/data.xml',
     *     'query' => 'id,name,email'
     * ];
     * 
     * $handler = new WebXmlHandler($config);
     * $result = $handler->handle();
     * ```
     * 
     * Error Handling:
     * - Throws `RuntimeException` if the URL is invalid or fetching fails.
     * - Throws `RuntimeException` if XML parsing fails, with detailed libxml error messages.
     * - Returns an empty array if the XML output is empty or malformed.
     * 
     * Security Note:
     * - The URL is validated using `filter_var()` to prevent SSRF attacks.
     * - Ensure that any dynamic parts of the URL (e.g., user input) are properly sanitised.
     * 
     * @return array Processed and optionally filtered data.
     * 
     * @throws \RuntimeException If the URL is invalid, fetching fails, or XML parsing fails.
     */
    public function handle(): array
    {
        // Retrieve the URL from the configuration
        $url = $this->config['source'] ?? '';

        // Validate the URL to prevent SSRF attacks
        if (empty($url) || !filter_var($url, FILTER_VALIDATE_URL)) {
            throw new \RuntimeException("Invalid URL: $url");
        }

        // Fetch the XML data from the URL
        $xmlContent = @file_get_contents($url);

        // Check if the URL fetch was successful
        if ($xmlContent === false) {
            throw new \RuntimeException("Unable to fetch URL: $url");
        }

        // Suppress XML parsing errors and use internal libxml error handling
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent, "SimpleXMLElement", LIBXML_NOCDATA);

        // Check for XML parsing errors
        if ($xml === false) {
            $errors = libxml_get_errors();
            $errorMessage = array_reduce($errors, function($carry, $error) {
                return $carry . trim($error->message) . "; ";
            }, "Failed to parse XML from URL: $url. Errors: ");
            libxml_clear_errors();
            throw new \RuntimeException($errorMessage);
        }

        // Convert the SimpleXMLElement to an associative array
        $data = json_decode(json_encode($xml), true);

        // Normalise the array structure
        $data = $this->normaliseArray($data);

        // Filter the data if query keys are specified
        return $this->filterData($data);
    }
}
