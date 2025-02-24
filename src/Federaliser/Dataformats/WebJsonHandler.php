<?php
namespace Federaliser\Dataformats;
/**
 * Class WebJsonHandler
 * Fetches JSON data via HTTP GET (from the URL in source), decodes it, normalizes it and optionally filters by keys.
 */
class WebJsonHandler extends AbstractHandler
{
    public function handle(): array
    {
        $url = $this->config['source'] ?? '';
        $json = @file_get_contents($url);
        if ($json === false) {
            throw new \RuntimeException("Unable to fetch URL: $url");
        }
        $data = json_decode($json, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decode error: " . json_last_error_msg());
        }
        $data = $this->normalizeArray($data);
        return $this->filterData($data);
    }
}