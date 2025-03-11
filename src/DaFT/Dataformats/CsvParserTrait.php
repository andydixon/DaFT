<?php
namespace DaFT\Dataformats;

/**
 * Trait CsvParserTrait
 * 
 * Provides functionality to parse CSV content into an associative array using the first row as the header.
 * 
 * This trait is designed to be used within classes that need to handle CSV data consistently.
 * It supports various line endings (`\r\n`, `\n`, `\r`) for cross-platform compatibility.
 * 
 * Usage Example:
 * ```
 * class CsvHandler {
 *     use CsvParserTrait;
 * 
 *     public function handle(string $csvContent) {
 *         return $this->parseCsv($csvContent);
 *     }
 * }
 * 
 * $handler = new CsvHandler();
 * $csv = "id,name,email\n1,Alice,alice@example.com\n2,Bob,bob@example.com";
 * $data = $handler->handle($csv);
 * 
 * // Result:
 * // [
 * //     ['id' => '1', 'name' => 'Alice', 'email' => 'alice@example.com'],
 * //     ['id' => '2', 'name' => 'Bob', 'email' => 'bob@example.com']
 * // ]
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
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Dataformats
 */
trait CsvParserTrait
{
    /**
     * Parses CSV content into an associative array using the first row as the header.
     * 
     * This method:
     * - Splits the CSV content into lines, handling various line endings.
     * - Uses the first non-empty line as the header row.
     * - Combines each subsequent row with the header to form associative arrays.
     * - Ignores rows that do not have the same number of columns as the header.
     * 
     * Example:
     * ```
     * $csv = "id,name,email\n1,Alice,alice@example.com\n2,Bob,bob@example.com";
     * $data = $this->parseCsv($csv);
     * 
     * // Result:
     * // [
     * //     ['id' => '1', 'name' => 'Alice', 'email' => 'alice@example.com'],
     * //     ['id' => '2', 'name' => 'Bob', 'email' => 'bob@example.com']
     * // ]
     * ```
     * 
     * Error Handling:
     * - If the CSV content is empty or malformed, an empty array is returned.
     * - Rows with mismatched column counts are ignored.
     * 
     * @param string $csv The CSV content as a string.
     * 
     * @return array Parsed CSV data as an array of associative arrays.
     */
    protected function parseCsv(string $csv): array
    {
        // Split CSV into lines, handling various line endings.
        $lines = preg_split('/\r\n|\n|\r/', $csv);

        // Remove any empty lines.
        $lines = array_filter($lines, function ($line) {
            return trim($line) !== '';
        });

        $data = [];
        if (empty($lines)) {
            return $data;
        }

        // The first non-empty line contains the headers.
        $header = str_getcsv(array_shift($lines));

        // Check for empty or invalid headers
        if (empty($header) || count($header) === 0) {
            return $data;
        }

        // Parse each line and combine with header to form an associative array.
        foreach ($lines as $line) {
            $row = str_getcsv($line);

            // Skip rows with a different number of columns than the header
            if (count($row) !== count($header)) {
                continue;
            }

            // Combine header with row to form an associative array
            $data[] = array_combine($header, $row);
        }

        return $data;
    }
}
