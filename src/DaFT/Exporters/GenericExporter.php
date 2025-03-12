<?php
/**
 * GenericExporter Class for Prometheus Exporters
 * 
 * This class serves as a base class for all exporters in the application. 
 * Although not technically abstract, it acts as a catch-all implementation that provides shared functionality.
 * If an exporter is not explicitly defined, this class returns a "501 Not Implemented" response.
 * 
 * It is intended to be extended by specific exporter classes such as:
 * - PrometheusExporter
 * - OpenMetricsExporter
 * - JSONExporter
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Exporters
 */

namespace DaFT\Exporters;

class GenericExporter 
{
    /**
     * Generic class everything is built from.
     * 
     * This method is a placeholder and is meant to be overridden by child classes.
     * If an exporter class does not implement its own `export()` method, this method will be called.
     * 
     * It sends an HTTP 501 (Not Implemented) status code along with a plain text response indicating
     * that the exporter is not defined.
     * 
     * Example Usage:
     * ```
     * GenericExporter::export($data, 200, $config);
     * ```
     * 
     * @param array $data            The data to be exported. Expected to be an associative array.
     * @param int $statusCode        The HTTP status code to be sent. (Not used in this implementation)
     * @param array $additionalConfig Additional configuration parameters for the exporter.
     * 
     * @return void
     */
    public static function export(array $data, int $statusCode, array $additionalConfig): void
    {
        // Set HTTP status to 501 Not Implemented
        header("HTTP/1.1 501 Not Implemented");

        // Set Content-Type to plain text
        header("Content-Type: text/plain");

        // Output error message and terminate script execution
        die('Exporter not defined.');
    }

    static $headerHandler;

    public static function setHeaderHandler($handler)
    {
        self::$headerHandler = $handler;
    }
}
