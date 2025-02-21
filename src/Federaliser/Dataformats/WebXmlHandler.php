<?php
namespace Federaliser\Dataformats;
/**
 * Class WebXmlHandler
 * Fetches XML data via HTTP GET (from the URL in hostname), converts it to an array and optionally filters by keys.
 */
class WebXmlHandler extends AbstractHandler
{
    public function handle(): array
    {
        $url = $this->config['hostname'] ?? '';
        $xmlContent = @file_get_contents($url);
        if ($xmlContent === false) {
            throw new \RuntimeException("Unable to fetch URL: $url");
        }
        $xml = @simplexml_load_string($xmlContent, "SimpleXMLElement", LIBXML_NOCDATA);
        if ($xml === false) {
            throw new \RuntimeException("Failed to parse XML from: $url");
        }
        // Convert XML to an associative array
        $json = json_encode($xml);
        $data = json_decode($json, true);
        $data = $this->normalizeArray($data);
        return $this->filterData($data);
    }
}