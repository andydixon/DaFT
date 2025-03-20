<?php
/**
 * Prometheus Exporter Class
 * 
 * This class handles the export of data in OpenMetrics format.
 * It loops through each row of the provided data, treating all but the last key-value pair as labels.
 * Labels are formatted as {key="escaped-value"} pairs.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 */

namespace DaFT\Exporters;

class PrometheusExporter extends GenericExporter {

    /**
     * Outputs data in OpenMetrics format with labels and metric values.
     * 
     * @param array $data Array of rows, where each row is an associative array of column names and values.
     * @param int $statusCode HTTP status code for the response.
     * @param array $additionalConfig Configuration options including 'identifier'.
     * 
     * @return void
     */
    public static function export($data, $statusCode, $additionalConfig): void {
        $headerHandler = self::$headerHandler ?? 'header';

        // Set the identifier or default to 'metric'
        $identifier = isset($additionalConfig['identifier']) ? $additionalConfig['identifier'] : 'metric';
        $headerHandler("Content-Type: text/plain");

        foreach ($data as $row) {
            // Get all keys and identify the last key as the metric
            $keys = array_keys($row);
            $lastKey = end($keys); // Last key is the metric

            // Extract labels and metric value
            $labels = array_filter($row, fn($key) => $key !== $lastKey, ARRAY_FILTER_USE_KEY);
            $metricValue = $row[$lastKey];

            // Construct labels string
            $labelsString = self::buildLabelsString($labels);

            // Output in OpenMetrics format
            echo "{$identifier}{$labelsString} $metricValue\n";
        }
    }

    /**
     * Builds a labels string in OpenMetrics format from an associative array.
     * 
     * @param array $labels Associative array of label keys and values.
     * 
     * @return string Labels string in OpenMetrics format.
     */
    private static function buildLabelsString(array $labels): string {
        if (empty($labels)) {
            return '';
        }

        $labelPairs = [];
        foreach ($labels as $key => $value) {
            $sanitisedKey = self::sanitiseLabelKey($key);
            $escapedValue = self::escapeLabelValue($value);
            $labelPairs[] = "{$sanitisedKey}=\"{$escapedValue}\"";
        }

        // Concatenate label pairs into {key="value", ...} format
        return '{' . implode(', ', $labelPairs) . '}';
    }

    /**
     * Sanitises label keys by removing invalid characters and converting them to snake_case.
     * 
     * @param string $key The label key to sanitise.
     * 
     * @return string Sanitised label key.
     */
    private static function sanitiseLabelKey($key): string {
        // Convert to lowercase, replace invalid characters with underscores
        $sanitisedKey = strtolower(preg_replace('/[^a-zA-Z0-9_]/', '_', $key));
        return $sanitisedKey;
    }

    /**
     * Escapes special characters in label values for OpenMetrics compatibility.
     * 
     * @param string $value The label value to escape.
     * 
     * @return string Escaped label value.
     */
    private static function escapeLabelValue($value): string {
        // Convert to string and escape backslashes and double quotes
        $escapedValue = addcslashes((string)$value, "\\\"");
        return $escapedValue;
    }
}
