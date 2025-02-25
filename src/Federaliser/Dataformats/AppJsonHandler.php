<?php
namespace Federaliser\Dataformats;

/**
 * Class AppJsonHandler
 * 
 * Executes a command (specified in `source`) that outputs JSON, decodes it, normalises it,
 * and optionally filters the result by keys specified in the query.
 * 
 * This class is useful for integrating external scripts or applications that provide JSON output.
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
 * $handler = new AppJsonHandler($config);
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
class AppJsonHandler extends AbstractHandler
{
    /**
     * Handles the execution of the command and processing of the JSON output.
     * 
     * This method:
     * - Retrieves the command from the `source` configuration.
     * - Executes the command using `shell_exec()`.
     * - Decodes the JSON output into an associative array.
     * - Normalises the array structure.
     * - Filters the data based on query keys, if specified.
     * 
     * Example:
     * ```
     * $config = [
     *     'source' => 'php /path/to/script.php',
     *     'query' => 'id,name,email'
     * ];
     * 
     * $handler = new AppJsonHandler($config);
     * $result = $handler->handle();
     * ```
     * 
     * Error Handling:
     * - Throws `RuntimeException` if the command execution fails.
     * - Throws `RuntimeException` if JSON decoding fails.
     * 
     * Security Note:
     * - The command is escaped using `escapeshellcmd()` to prevent injection attacks.
     * - Ensure that any dynamic parts of the command (e.g., user input) are properly sanitised.
     * 
     * @return array Processed and optionally filtered data.
     * 
     * @throws \RuntimeException If the command execution or JSON decoding fails.
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

        // Decode the output as JSON
        $data = json_decode($output, true);

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
