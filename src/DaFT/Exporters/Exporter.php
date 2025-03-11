<?php
/**
 * Exporter Class for Metric Identification
 * 
 * This class is responsible for identifying the requested export format based on the URI.
 * It supports the following formats:
 * - Prometheus
 * - OpenMetrics
 * - JSON
 * 
 * The export format is determined by the last segment of the request URI.
 * 
 * Usage:
 * ```
 * $exportType = Exporter::identify();
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Exporters
 */

namespace DaFT\Exporters;

use DaFT\Helpers;

class Exporter
{
    /**
     * Exporter Type Constants
     * 
     * These constants represent the supported exporter types.
     */
    const PROMETHEUS_EXPORTER = 1;
    const OPENMETRICS_EXPORTER = 2;
    const JSON_EXPORTER = 3;
    const TELEGRAF_EXPORTER = 4;

    /**
     * Identifies the export format based on the request URI.
     * 
     * This method examines the last segment of the request URI and maps it to one of the supported exporter types:
     * - "prometheus" → PROMETHEUS_EXPORTER
     * - "openmetrics" → OPENMETRICS_EXPORTER
     * - "json" → JSON_EXPORTER
     * 
     * If the URI segment does not match any of the supported formats, it defaults to JSON_EXPORTER.
     * 
     * Example:
     * ```
     * // Given a request URI like "/metrics/prometheus"
     * // This will return Exporter::PROMETHEUS_EXPORTER
     * $exportType = Exporter::identify();
     * ```
     * 
     * @return int One of the following constants:
     *             - self::PROMETHEUS_EXPORTER (1)
     *             - self::OPENMETRICS_EXPORTER (2)
     *             - self::JSON_EXPORTER (3)
     */
    public static function identify(): int
    {
        // Get the cleaned request URI using the Helpers class
        $requestUri = Helpers::cleanUri();

        // Extract the last segment of the URI and convert it to lowercase
        $format = strtolower(basename($requestUri));

        // Determine the exporter type based on the last segment
        switch ($format) {
            case "prometheus":
                return self::PROMETHEUS_EXPORTER;

            case "openmetrics":
                return self::OPENMETRICS_EXPORTER;

            case "json":
                return self::JSON_EXPORTER;
            case 'telegraf':
                return self::TELEGRAF_EXPORTER;
            default:
                // Default to JSON_EXPORTER if no match is found
                return self::JSON_EXPORTER;
        }
    }
}
