<?php

namespace Federaliser\Dataformats;

/**
 * Class WebXmlHandler
 *
 * Fetches XML via HTTP, extracts a specific path, and filters fields.
 */
class WebXmlHandler extends AbstractXmlHandler
{
    /**
     * Fetches XML from the configured web source, processes it, and returns structured output.
     *
     * @return array Processed XML output.
     * @throws \RuntimeException If the HTTP request fails or XML parsing fails.
     */
    public function handle(): array
    {
        $url = $this->config['source'] ?? '';

        // Fetch XML from the URL
        $xmlContent = @file_get_contents($url);
        if ($xmlContent === false) {
            throw new \RuntimeException("Unable to fetch XML from URL: $url");
        }

        // Parse and process XML
        $data = $this->parseXml($xmlContent);
        return $this->processXmlData($data);
    }
}
