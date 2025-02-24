<?php
namespace Federaliser\Dataformats;

use PDO;
use PDOException;

/**
 * Class PrometheusHandler
 *
 * Executes queries against a Prometheus server via a simple cURL call.
 */
class PrometheusHandler extends AbstractHandler
{
    public function handle(): array
    {
        $host  = rtrim($this->params['source'] ?? '', '/');
        $port  = $this->params['port'] ?? 9090;
        $query = $this->params['query'] ?? 'up';
        $url   = "{$host}:{$port}/api/v1/query?query=" . urlencode($query);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($statusCode !== 200) {
            return ['error' => "Prometheus query failed with HTTP status: $statusCode", 'query' => $query];
        }

        $json = json_decode($response, true);
        return $json ?? ['error' => 'Invalid JSON response from Prometheus', 'query' => $query];
    }
}