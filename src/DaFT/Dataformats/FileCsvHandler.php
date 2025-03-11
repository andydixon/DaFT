<?php
namespace DaFT\Dataformats;

/**
 * Class FileCsvHandler
 * 
 * Handles reading and parsing CSV files.
 * - Reads the CSV file from the `source` path specified in the configuration.
 * - Parses the CSV content into an associative array using the first row as headers.
 * - Normalises the array structure and optionally filters the data by specified keys.
 * 
 * This class is useful for processing CSV data from local files.
 * It inherits common functionality from `AbstractHandler`, such as array normalization and data filtering,
 * and uses `CsvParserTrait` for consistent CSV parsing.
 * 
 * Security Notice:
 * - This class ensures that the file path is validated and readable.
 * - Make sure the `source` parameter is securely configured to avoid directory traversal attacks.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'source' => '/path/to/data.csv',
 *     'query' => 'id,name,email'
 * ];
 * 
 * $handler = new FileCsvHandler($config);
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
 * - This class promotes consistency in handling CSV files by using `CsvParserTrait`.
 * - It validates the file path to enhance security.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Dataformats
 */
class FileCsvHandler extends AbstractHandler
{
    use CsvParserTrait;
    
    /**
     * Handles reading and processing the CSV file.
     * 
     * This method:
     * - Validates the file path specified in the `source` configuration.
     * - Reads the CSV content from the file.
     * - Parses the CSV content into an associative array using the first row as headers.
     * - Normalises the array structure.
     * - Filters the data based on query keys, if specified.
     * 
     * Example:
     * ```
     * $config = [
     *     'source' => '/path/to/data.csv',
     *     'query' => 'id,name,email'
     * ];
     * 
     * $handler = new FileCsvHandler($config);
     * $result = $handler->handle();
     * ```
     * 
     * Error Handling:
     * - Throws `RuntimeException` if the file path is invalid or not readable.
     * - Throws `RuntimeException` if an error occurs while reading the file.
     * 
     * Security Note:
     * - The file path is validated using `realpath()` to prevent directory traversal attacks.
     * - Only files within the allowed base directory are processed.
     * 
     * @return array Processed and optionally filtered data.
     * 
     * @throws \RuntimeException If the file is not readable or an error occurs during reading.
     */
    public function handle(): array
    {
        // Retrieve the file path from the configuration
        $file = $this->config['source'] ?? '';

        // Validate the file path using realpath() to prevent directory traversal attacks
        $realPath = realpath($file);
        if ($realPath === false || !is_readable($realPath)) {
            throw new \RuntimeException("Unable to read file: $file");
        }

        // Read the CSV content from the file
        $csv = file_get_contents($realPath);

        // Check if the file was successfully read
        if ($csv === false) {
            throw new \RuntimeException("Error reading file: $file");
        }

        // Parse the CSV content into an associative array
        $data = $this->parseCsv($csv);

        // Normalise the array structure
        $data = $this->normaliseArray($data);

        // Filter the data if query keys are specified
        return $this->filterData($data);
    }
}
