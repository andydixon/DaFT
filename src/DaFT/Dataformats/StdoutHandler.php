<?php
namespace DaFT\Dataformats;

/**
 * Class StdoutHandler
 * 
 * Executes an external application (specified in `source`) and processes its STDOUT.
 * - If a `query` is set, it is assumed to be a regex pattern with named capturing groups.
 * - If no `query` is set, the raw output is returned as a single-element array.
 * - This class supports complex text parsing scenarios using regex patterns.
 * - It inherits common functionality from `GenericHandler`, such as data normalization and filtering.
 * 
 * Security Notice:
 * - The command specified in `source` is executed using `shell_exec()`.
 * - This can be a security risk if user input is directly used. Ensure that commands are properly sanitised.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'type' => 'stdout',
 *     'source' => 'php /path/to/script.php',
 *     'query' => '/id=(?P<id>\d+),name=(?P<name>[A-Za-z]+),email=(?P<email>[^,]+)/'
 * ];
 * 
 * $handler = new StdoutHandler($config);
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
 * - This class promotes flexible text parsing using named capturing groups in regex patterns.
 * - It supports diverse use cases by allowing custom commands and complex text processing.
 * 
 * Security Recommendations:
 * - Always sanitise user input if it influences the command to prevent command injection.
 * - Consider using a whitelist of allowed commands for enhanced security.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Dataformats
 */
class StdoutHandler extends GenericHandler
{
    /**
     * Handles executing the configured command and processing its STDOUT.
     * 
     * This method:
     * - Retrieves the command from the `source` configuration.
     * - Escapes the command to prevent command injection.
     * - Executes the command using `shell_exec()` and captures the output.
     * - If a `query` is set, it is assumed to be a regex pattern with named capturing groups.
     * - Uses `preg_match_all()` to extract named groups into associative arrays.
     * - If no `query` is set, returns the raw output as a single-element array.
     * - Normalises the array structure and optionally filters the data by query keys.
     * 
     * Example:
     * ```
     * $config = [
     *     'type' => 'stdout',
     *     'source' => 'php /path/to/script.php',
     *     'query' => '/id=(?P<id>\d+),name=(?P<name>[A-Za-z]+),email=(?P<email>[^,]+)/'
     * ];
     * 
     * $handler = new StdoutHandler($config);
     * $result = $handler->handle();
     * ```
     * 
     * Error Handling:
     * - Throws `RuntimeException` if the command execution fails.
     * - Returns an empty array if the regex pattern does not match any data.
     * 
     * Security Note:
     * - The command is escaped using `escapeshellcmd()` to prevent injection attacks.
     * - Ensure that any dynamic parts of the command (e.g., user input) are properly sanitised.
     * 
     * @return array Processed and optionally filtered data.
     * 
     * @throws \RuntimeException If the command execution fails.
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

        // Check if a query pattern is set
        if (isset($this->config['query']) && !empty($this->config['query'])) {
            $pattern = $this->config['query'];
            $matches = [];

            // Use preg_match_all to capture named groups
            if (preg_match_all($pattern, $output, $matches, PREG_SET_ORDER)) {
                // Each $match will have numeric keys as well as named keys if provided in the regex.
                $results = [];
                foreach ($matches as $match) {
                    $entry = [];
                    foreach ($match as $key => $value) {
                        if (!is_int($key)) { // Only include named groups.
                            $entry[$key] = $value;
                        }
                    }
                    $results[] = $entry;
                }

                // Normalise the array structure
                $results = $this->normaliseArray($results);

                // Filter the data if query keys are specified
                return $this->filterData($results);
            }
            // Return an empty array if no matches are found
            return [];
        }

        // If no query pattern is set, return the raw output as a single-element array
        return [$output];
    }
}
