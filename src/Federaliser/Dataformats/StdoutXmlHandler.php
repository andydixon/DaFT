<?php
namespace Federaliser\Dataformats;

/**
 * Class StdoutXmlHandler
 * 
 * Fetches XML data from the stdout of a command, converts it to an associative array,
 * normalises the array structure, and optionally filters the data by specified keys.
 * 
 * This class is useful for integrating shell scripts or command-line tools that output XML data.
 * It inherits common functionality from `AbstractHandler`, such as data normalization and filtering.
 * 
 * Security Notice:
 * - The command specified in `source` is executed using `shell_exec()`.
 * - This can be a security risk if user input is directly used. Ensure that commands are properly sanitised.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'type' => 'stdout-xml',
 *     'source' => 'php /path/to/xml_generator.php',
 *     'query' => 'id,name,email'
 * ];
 * 
 * $handler = new StdoutXmlHandler($config);
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
 * - This class promotes consistency in handling XML data from command-line tools.
 * - It validates and escapes the command before execution to enhance security.
 * - It uses internal libxml error handling for robust XML parsing.
 * 
 * Security Recommendations:
 * - Always sanitise user input if it influences the command to prevent command injection.
 * - Consider using a whitelist of allowed commands for enhanced security.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser\Dataformats
 */
class StdoutXmlHandler extends AbstractHandler
{
    /**
     * Handles executing the configured command and processing the XML output.
     * 
     * This method:
     * - Retrieves the command from the `source` configuration.
     * - Escapes the command to prevent command injection.
     * - Executes the command using `shell_exec()` and captures the XML output.
     * - Parses the XML string into a `SimpleXMLElement`.
     * - Converts the XML to an associative array.
     * - Normalises the array structure.
     * - Filters the data based on query keys, if specified.
     * 
     * Example:
     * ```
     * $config = [
     *     'type' => 'stdout-xml',
     *     'source' => 'php /path/to/xml_generator.php',
     *     'query' => 'id,name,email'
     * ];
     * 
     * $handler = new StdoutXmlHandler($config);
     * $result = $handler->handle();
     * ```
     * 
     * Error Handling:
     * - Throws `RuntimeException` if the command execution fails.
     * - Throws `RuntimeException` if XML parsing fails, with detailed libxml error messages.
     * - Returns an empty array if the XML output is empty or malformed.
     * 
     * Security Note:
     * - The command is escaped using `escapeshellcmd()` to prevent injection attacks.
     * - Ensure that any dynamic parts of the command (e.g., user input) are properly sanitised.
     * 
     * @return array Processed and optionally filtered data.
     * 
     * @throws \RuntimeException If the command execution fails or XML parsing fails.
     */
    public function handle(): array
    {
        // Retrieve the command from the configuration
        $command = $this->config['source'] ?? '';

        // Escape the command to prevent command injection
        $escapedCommand = escapeshellcmd($command);

        // Execute the command and capture the output
        $xmlContent = shell_exec($escapedCommand);

        // Check if the command execution was successful
        if ($xmlContent === null) {
            throw new \RuntimeException("Unable to execute command: $escapedCommand");
        }

        // Suppress XML parsing errors and use internal libxml error handling
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($xmlContent, "SimpleXMLElement", LIBXML_NOCDATA);

        // Check for XML parsing errors
        if ($xml === false) {
            $errors = libxml_get_errors();
            $errorMessage = array_reduce($errors, function($carry, $error) {
                return $carry . trim($error->message) . "; ";
            }, "Failed to parse XML output from command: $escapedCommand. Errors: ");
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
