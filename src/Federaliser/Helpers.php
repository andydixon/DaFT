<?php
/**
 * Federaliser Helper functions
 * For a change lets not just dump functions here, there and everywhere. You're better than that.
 *
 * @author Andy Dixon <andy@andydixon.com>
 * @created 2025-01-16
 */

namespace Federaliser;

class Helpers
{


    /**
     * Check to see if prometheus is required exporter
     * @return bool
     */
    public static function isPrometheusExporter(): bool
    {
        $requestUri = self::cleanUri();
        return str_ends_with($requestUri, '/prometheus');
    }

    /**
     * Clean the URI to get the route
     * @param $removePrometheus boolean remove prometheus exporter tag if it is there
     * @return string
     */
    public static function cleanUri(bool $removePrometheus=false): string {
        // Simplest possible approach: parse the REQUEST_URI
        $requestUri = $_SERVER['REQUEST_URI'] ?? '/';

        // Strip any query string (e.g. ?param=value)
        if (($pos = strpos($requestUri, '?')) !== false) {
            $requestUri = substr($requestUri, 0, $pos);
        }

        // Ensure it doesn't start or end with slash
        $requestUri= trim($requestUri, '/');

        if($removePrometheus) {
            // Check to see if this is prometheus exporter
            $suffix = '/prometheus';
            if (str_ends_with($requestUri, $suffix)) {
                $requestUri = substr($requestUri, 0, -strlen($suffix)); // Remove the suffix
            }
        }

        return $requestUri;

    }
}
