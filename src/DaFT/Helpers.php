<?php
/**
 * DaFT Helper Functions
 * 
 * This class contains utility functions for the DaFT application.
 * It is designed to centralise common functionalities to promote code reuse and maintainability.
 * 
 * This approach avoids scattering helper functions throughout the application, 
 * maintaining cleaner and more organised code.
 * 
 * Usage Example:
 * ```
 * // Clean the request URI
 * $cleanUri = Helpers::cleanUri(true);
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT
 */

namespace DaFT;

class Helpers
{
    /**
     * Cleans the REQUEST_URI to get the route path.
     * 
     * This method:
     * - Retrieves the REQUEST_URI from the global `$_SERVER` superglobal.
     * - Strips query strings (e.g., `?param=value`).
     * - Removes leading and trailing slashes.
     * - Optionally removes the `/prometheus` suffix if `$removePrometheus` is `true`.
     * 
     * Example Usage:
     * ```
     * // Basic usage without removing Prometheus suffix
     * $route = Helpers::cleanUri();
     * 
     * // Usage with Prometheus suffix removal
     * $route = Helpers::cleanUri(true);
     * ```
     * 
     * @param bool $removeExporter Optional. If true, removes the `/<exporter>` suffix from the URI.
     * 
     * @return string Cleaned route path.
     */
    public static function cleanUri(bool $removeExporter = false): string
    {
        // Get the REQUEST_URI or default to root
            $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Strip any query string (e.g., ?param=value)
        if (($pos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        // Trim leading and trailing slashes
        $requestUri = trim($requestUri, '/');

        // Optionally remove the exporter suffix
        if ($removeExporter) {
            $parts = explode("/",$requestUri);
            if (count($parts)>1) {
                $requestUri = substr($requestUri, 0, -strlen($parts[count($parts)-1]));
                $requestUri = rtrim($requestUri, '/');
            }
        }

        return $requestUri;
    }

    public static function arrayFlatten(array $array): array
    {
        $result = [];
        array_walk_recursive($array, function($value, $key) use (&$result) {
            $result[$key] = $value;
        });
        return $result;
    }
    public static function sanitiseString(string $input): string
    {
        return htmlspecialchars(strip_tags($input), ENT_QUOTES, 'UTF-8');
    }

}
