<?php

namespace DaFT\Dataformats;

/**
 * Class AbstractXmlHandler
 *
 * Provides shared XML extraction and filtering logic for WebXmlHandler and AppXmlHandler.
 */
abstract class AbstractXmlHandler extends AbstractHandler
{
    /**
     * Parses XML into an associative array.
     *
     * @param string $xmlContent The raw XML string.
     * @return array The parsed XML as an array.
     * @throws \RuntimeException If the XML cannot be parsed.
     */
    protected function parseXml(string $xmlContent): array
    {
        $xml = @simplexml_load_string($xmlContent, "SimpleXMLElement", LIBXML_NOCDATA);
        if ($xml === false) {
            throw new \RuntimeException("Failed to parse XML content.");
        }

        return json_decode(json_encode($xml), true) ?? [];
    }

    /**
     * Extracts a nested XML path using dot notation.
     *
     * @param array $data The parsed XML array.
     * @param string|null $path The dot-notated path (e.g., "data.items.item").
     * @return mixed Extracted data (array, object, or empty array if not found).
     */
    protected function extractXmlPath(array $data, ?string $path)
    {
        if (!$path) {
            return $data; // No path specified, return full XML
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
     * @param mixed $data The target XML data (array or object).
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
     * Processes XML data using optional path extraction and field filtering.
     *
     * @param array $data The parsed XML array.
     * @return array Processed XML output.
     */
    protected function processXmlData(array $data): array
    {
        $data = $this->extractXmlPath($data, $this->config['xml_path'] ?? null);
        return $this->filterFields($data, $this->config['fields'] ?? null);
    }
}
