<?php

namespace Federaliser\Dataformats;

/**
 * Class WebJsonHandler
 *
 * Fetches JSON data from a remote API (via HTTP GET), processes it, and returns structured results.
 * Supports:
 * - Extracting a specific path from JSON (using dot notation).
 * - Filtering specific fields from the extracted data.
 */
class WebJsonHandler extends AbstractJsonHandler
{
    /**
     * Fetches JSON from the configured web source, processes it, and returns structured output.
     *
     * @return array Processed JSON output.
     * @throws \RuntimeException If the HTTP request fails or JSON decoding fails.
     */
    public function handle(): array
    {
        $url = $this->config['source'] ?? '';

        // Fetch JSON from the URL
        $json = @file_get_contents($url);
        if ($json === false) {
            throw new \RuntimeException("Unable to fetch URL: $url");
        }

        // Decode JSON response
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decode error: " . json_last_error_msg());
        }

        // Process JSON data (extract path, filter fields, normalize)
        return $this->processJsonData($data);
    }
}
