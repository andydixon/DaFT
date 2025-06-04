<?php

namespace DaFT\Dataformats;

/**
 * Class StarlinkHandler
 * 
 * Handles communication with a Starlink dish via gRPC to collect and format status metrics.
 * This handler connects to a Starlink dish, retrieves its status data, and transforms it 
 * into a standardized metrics format.
 * 
 * Configuration requirements:
 * - 'source': The IP address and port of the Starlink dish (default: '192.168.100.1:9200')
 * 
 * External dependencies:
 * - 'grpcurl': Command-line tool for interacting with gRPC servers
 *   Must be installed and accessible in PATH or specified via grpcurl config.ini variable
 * 
 * @author Andy Dixon
 * @created 2025-03-30
 * @namespace DaFT\Dataformats
 */
class StarlinkHandler extends GenericHandler
{
    /**
     * Retrieves and processes status data from a Starlink dish.
     * 
     * This method:
     * 1. Connects to the configured Starlink dish using grpcurl
     * 2. Retrieves status data via gRPC API call to SpaceX.API.Device.Device/Handle
     * 3. Flattens the hierarchical JSON response into a list of metrics
     * 4. Converts string values to labels and numeric values to metrics
     * 
     * @return array An array of metrics, where each metric is an array of [name, value]
     * @throws \Exception Implicitly exits with HTTP error codes on connection/data errors
     */
    public function handle(): array
    {
        $dish = $this->config['source'] ?? '192.168.100.1:9200';

        $grpcurl = $this->config['grpcurl']   ?: 'grpcurl';

        // Get the JSON
        $cmd = escapeshellcmd($grpcurl) .
            ' -plaintext -max-time 2 -format json ' .
            ' -d \'{"get_status":{}}\' ' .
            escapeshellarg($dish) .
            ' SpaceX.API.Device.Device/Handle';

        exec($cmd, $out, $rc);
        if ($rc !== 0) {
            http_response_code(500);
            return ['error' => ['message'=>'Error executing grpcurl','command'=>$cmd]];
            exit;
        }

        $body   = json_decode(implode("\n", $out), true);
        $status = $body["dishGetStatus"] ?? null;
        if (!$status) {
            return ['error' => ['message'=>'No dishGetStatus data received from Starlink dish.','retrieved'=>$body]];
        }

        // flatten + emit metrics
        $metrics = [];
        $labels  = [];                 // strings/enums stuffed here for one build-info line
        $skip    = ['alert_bits'];     // any keys you explicitly don’t want

        $push = function (string $name, $value) use (&$metrics) {
            $metrics[] = [$name, $value];
        };

        /* recursive walk */
        $walk = function ($node, string $prefix = '') use (&$walk, &$push, &$labels, $skip) {
            foreach ($node as $k => $v) {
                if (in_array($k, $skip, true)) continue;
                $key = $prefix . preg_replace('/([a-z])([A-Z])/', '$1_$2', $k); // camelCase→snake
                $key = strtolower($key);

                if (is_array($v)) {
                    /* numeric-index array → label “idx” */
                    if (array_keys($v) === range(0, count($v) - 1)) {
                        foreach ($v as $i => $vv) $walk($vv, $key . '{idx="' . $i . '"}_');
                    } else {
                        $walk($v, $key . '_');
                    }
                } elseif (is_bool($v)) {
                    $push($key, $v ? 1 : 0);
                } elseif (is_int($v) || is_float($v) || $v === 0) {
                    $push($key, $v);
                }
            }
        };
        $walk($status);
        return $metrics;
    }
}
