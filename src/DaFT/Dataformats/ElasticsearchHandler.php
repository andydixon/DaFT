<?php

namespace DaFT\Dataformats;

class ElasticsearchHandler extends AbstractJsonHandler
{
    /**
     * Fetches JSON data from Elasticsearch, processes it, and returns structured output. 
     *
     * Config keys expected under [elasticsearch]:
     *   source       –   hostname or IP of ES server
     *   port         –   TCP port (e.g. 9200)
     *   username     –   if secured
     *   password     –   if secured
     *   default_db   –   index name (or alias)
     *   query        –   inline JSON string or path to a .json file
     *   timeframe    –   e.g. "now-1m" (optional; used to inject into the query)
     *
     * @return array
     * @throws \RuntimeException on communication or JSON errors.
     */
    public function handle(): array
    {
        // --- 1. Build URL and auth ---
        $host      = rtrim($this->config['source'] ?? '', '/');
        $port      = $this->config['port'] ?? 9200;
        $index     = $this->config['default_db'] ?? '_all';
        $url       = "{$host}:{$port}/{$index}/_search";

        $user      = $this->config['username'] ?? null;
        $pass      = $this->config['password'] ?? null;
        $auth      = $user !== null ? "{$user}:{$pass}@" : '';

        // --- 2. Load the query body ---
        $rawQuery = $this->config['query'] ?? '{}';
        if (is_file($rawQuery)) {
            $body = file_get_contents($rawQuery);
        } else {
            $body = $rawQuery;
        }
        if ($body === false) {
            throw new \RuntimeException("Unable to load query from {$rawQuery}");
        }

        // Optionally inject a timeframe into the query (assumes placeholder {{timeframe}})
        if (!empty($this->config['timeframe'])) {
            $body = str_replace('{{timeframe}}', $this->config['timeframe'], $body);
        }

        // --- 3. Execute HTTP POST to Elasticsearch ---
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,      $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_POST,     true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $body);
        curl_setopt($ch, CURLOPT_HTTPHEADER, ['Content-Type: application/json']);
        if ($user !== null) {
            curl_setopt($ch, CURLOPT_USERPWD, "{$user}:{$pass}");
        }
        $json = curl_exec($ch);
        if ($json === false) {
            $err = curl_error($ch);
            curl_close($ch);
            throw new \RuntimeException("Elasticsearch request failed: {$err}");
        }
        $code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        if ($code < 200 || $code >= 300) {
            throw new \RuntimeException("Elasticsearch returned HTTP {$code}: {$json}");
        }

        // --- 4. Decode JSON ---
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decode error: " . json_last_error_msg());
        }

        // 5. Drill into the hits array (or wherever your metrics live)
        //    You can override this if you want a different path.
        $metrics = $data['hits']['hits'] ?? [];

        // 6. Let AbstractJsonHandler do its work: path-extraction, field filtering,
        //    regex labeling, summing/aggregation, etc.
        return $this->processJsonData($metrics);
    }
}
