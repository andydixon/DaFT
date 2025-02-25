<?php
namespace Federaliser\Dataformats;

/**
 * Interface DataFormatHandlerInterface
 * 
 * This interface defines the contract for all data format handlers in the Federaliser application.
 * It ensures consistency and standardization by requiring the implementation of the `handle()` method.
 * 
 * All data format handlers must:
 * - Implement the `handle()` method.
 * - Return a normalised array of data.
 * - Handle input processing from various sources (e.g., URL, command line, file).
 * 
 * Usage Example (in a class implementing this interface):
 * ```
 * class JsonHandler implements DataFormatHandlerInterface {
 *     public function handle(): array {
 *         $data = file_get_contents('data.json');
 *         return json_decode($data, true);
 *     }
 * }
 * 
 * $handler = new JsonHandler();
 * $result = $handler->handle();
 * 
 * // Result:
 * // [
 * //     ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
 * //     ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com']
 * // ]
 * ```
 * 
 * Design Considerations:
 * - This interface promotes consistency across different data format handlers.
 * - It allows the application to utilise polymorphism, enabling flexible data handling.
 * 
 * Implementing Classes:
 * - `AppJsonHandler`: Handles JSON data sources.
 * - `AppXmlHandler`: Handles XML data sources.
 * - `CsvHandler`: Handles CSV data sources.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser\Dataformats
 */
interface DataFormatHandlerInterface
{
    /**
     * Process the input (from URL, command, file, etc.) and return a normalised array.
     * 
     * The `handle()` method is responsible for:
     * - Processing the input data.
     * - Normalizing the data structure into an associative array.
     * - Handling errors gracefully and returning a consistent data format.
     * 
     * Example:
     * ```
     * $handler = new AppJsonHandler(['source' => 'data.json']);
     * $data = $handler->handle();
     * ```
     * 
     * Expected Return Format:
     * ```
     * [
     *     ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
     *     ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com']
     * ]
     * ```
     * 
     * Error Handling:
     * - Implementing classes should handle errors gracefully.
     * - Consistent return types (empty array on failure).
     * 
     * @return array Normalised array of processed data.
     */
    public function handle(): array;
}
