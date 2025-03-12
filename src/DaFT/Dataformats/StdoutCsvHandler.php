<?php
namespace DaFT\Dataformats;

/**
 * Class StdoutCsvHandler
 * 
 * Executes a command that outputs CSV data to stdout, then parses the CSV,
 * normalises the array structure, and optionally filters the data by specified keys.
 * 
 * This class is useful for integrating shell scripts or command-line tools that output CSV data.
 * It inherits common functionality from `GenericHandler`, such as data normalization and filtering,
 * and uses `CsvParserTrait` for consistent CSV parsing.
 * 
 * Security Notice:
 * - The command specified in `source` is executed using `shell_exec()`.
 * - This can be a security risk if user input is directly used. Ensure that commands are properly sanitised.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'type' => 'stdout-csv',
 *     'source' => 'php /path/to/csv_generator.php',
 *     'query' => 'id,name,email'
 * ];
 * 
 * $handler = new StdoutCsvHandler($config);
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
 * - This class promotes consistency in handling CSV data from command-line tools by using `CsvParserTrait`.
 * - It validates the command before execution to enhance security.
 * 
 * Security Recommendations:
 * - Always sanitise user input if it influences the command to prevent command injection.
 * - Consider using a whitelist of allowed commands for enhanced security.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Dataformats
 */
class StdoutCsvHandler extends GenericHandler
{
    use CsvParserTrait;
    
    /**
     * Handles executing the configured command and processing the CSV output.
     * 
     * This method:
     * - Retrieves the command from the `source` configuration.
     * - Escapes the command to prevent command injection.
     * - Executes the command using `shell_exec()` and captures the output.
     * - Parses the CSV output into an associative array using the first row as headers.
     * - Normalises the array structure.
     * - Filters the data based on query keys, if specified.
     * 
     * Example:
     * ```
     * $config = [
     *     'type' => 'stdout-csv',
     *     'source' => 'php /path/to/csv_generator.php',
     *     'query' => 'id,name,email'
     * ];
     * 
     * $handler = new StdoutCsvHandler($config);
     * $result = $handler->handle();
     * ```
     * 
     * Error Handling:
     * - Throws `RuntimeException` if the command execution fails.
     * - Returns an empty array if the CSV output is malformed or empty.
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
            throw new \RuntimeException("Unable to execute command: $escapedCommand");
        }

        // Parse the CSV output into an associative array
        $data = $this->parseCsv($output);

        // Normalise the array structure
        $data = $this->normaliseArray($data);

        // Filter the data if query keys are specified
        return $this->filterData($data);
    }
}
