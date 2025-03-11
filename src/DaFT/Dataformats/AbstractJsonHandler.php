<?php

namespace DaFT\Dataformats;

/**
 * Class AbstractJsonHandler
 *
 * Provides shared JSON processing logic, including:
 * - Extracting nested JSON paths using dot notation.
 * - Filtering specific fields from JSON objects or arrays.
 * - Handling both structured objects and lists of objects.
 * 
 * Any JSON-based handlers (e.g., WebJsonHandler, AppJsonHandler) should extend this class.
 */
abstract class AbstractJsonHandler extends AbstractHandler
{
    /**
     * Extracts a nested JSON path using dot notation.
     *
     * @param array $data The original JSON-decoded array.
     * @param string|null $path The dot-notated path (e.g., "data.items").
     * @return mixed Extracted data (array, object, or empty array if not found).
     */
    protected function extractJsonPath(array $data, ?string $path)
    {
        if (!$path) {
            return $data; // No path specified, return full JSON
        }

        $keys = explode('.', $path);
        foreach ($keys as $key) {
            if (isset($data[$key])) {
                $data = $data[$key];
            } else {
                return []; // Return empty array if path does not exist
            }
        }

        return is_array($data) ? $data : [$data]; // Ensure structured data
    }

    /**
     * Filters an array or object to only include specified fields.
     *
     * @param mixed $data The target JSON data (array or object).
     * @param string|null $fields A comma-separated list of fields to extract.
     * @return array Filtered data.
     */
    protected function filterFields($data, ?string $fields): array
    {
        if (!$fields) {
            return is_array($data) ? $data : [$data]; // No fields specified, return original data
        }

        $fieldList = array_map('trim', explode(',', $fields));

        if (isset($data[0]) && is_array($data[0])) {
            // If the data is an array of objects
            return array_map(fn($item) => array_intersect_key($item, array_flip($fieldList)), $data);
        } elseif (is_array($data)) {
            // If the data is a single object
            return [array_intersect_key($data, array_flip($fieldList))];
        }

        return [];
    }

    /**
     * Processes JSON data using optional path extraction and field filtering.
     *
     * @param array $data The JSON-decoded array.
     * @return array Processed JSON output.
     */
    protected function processJsonData(array $data): array
    {
        $data = $this->extractJsonPath($data, $this->config['json_path'] ?? null);
        return $this->filterFields($data, $this->config['fields'] ?? null);
    }
}
