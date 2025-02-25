<?php
namespace Federaliser\Dataformats;

/**
 * Class AppXmlHandler
 * 
 * Executes a command (specified in `source`) that outputs XML, converts it to an array,
 * normalises the array structure, and optionally filters the data by specified keys.
 * 
 * This class is useful for integrating external scripts or applications that provide XML output.
 * It inherits common functionality from `AbstractHandler`, such as array normalization and data filtering.
 * 
 * Security Notice:
 * - The command specified in `source` is executed using `shell_exec()`.
 * - This can be a security risk if user input is directly used. Ensure that commands are properly sanitised.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'source' => 'php /path/to/script.php',
 *     'query' => 'id,name,email'
 * ];
 * 
 * $handler = new AppXmlHandler($config);
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
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser\Dataformats
 */
class AppXmlHandler extends AbstractHandler
{
    /**
     * Handles the execution of the command and processing of the XML output.
     * 
     * This method:
     * - Executes the command specified in the `source` configuration.
     * - Parses the XML output into a SimpleXMLElement.
     * - Converts the XML to an associative array.
     * - Normalises the array structure.
     * - Filters the data based on query keys, if specified.
     * 
     * Example:
     * ```
     * $config = [
     *     'source' => 'php /path/to/xml_generator.php',
     *     'query' => 'id,name,email'
     * ];
     * 
     * $handler = new AppXmlHandler($config);
     * $result = $handler->handle();
     * ```
     * 
     * Error Handling:
     * - Throws `RuntimeException` if the command execution fails.
     * - Throws `RuntimeException` if XML parsing fails.
     * 
     * Security Note:
     * - The command is escaped using `escapeshellcmd()` to prevent injection attacks.
     * - Ensure that any dynamic parts of the command (e.g., user input) are properly sanitised.
     * 
     * @return array Processed and optionally filtered data.
     * 
     * @throws \RuntimeException If the command execution or XML parsing fails.
     */
    public function handle(): array
    {
        // Retrieve the command from the configuration
        $command = $this->config['source'] ?? '';

        // Escape the command to prevent command injection
        $escapedCommand = escapeshellcmd($command);

        // Execute the command and capture the output
        $output = shell_exec($escapedCommand);

        // Check if the command execution was successful
        if ($output === null) {
            throw new \RuntimeException("Command execution failed: $escapedCommand");
        }

        // Suppress XML parsing errors and attempt to load the XML string
        libxml_use_internal_errors(true);
        $xml = simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);

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
