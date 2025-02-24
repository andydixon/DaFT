<?php
namespace Federaliser\Dataformats;
/**
 * Class StdoutXmlHandler
 * Fetches XML data from stdout of a command, converts it to an array and optionally filters by keys.
 */
class StdoutXmlHandler extends AbstractHandler
{
    public function handle(): array
    {
        $command = $this->config['source'] ?? '';
        $xmlContent = shell_exec($command);
        if ($xmlContent === null) {
            throw new \RuntimeException("Unable to execute command: $command");
        }

        $xml = @simplexml_load_string($xmlContent, "SimpleXMLElement", LIBXML_NOCDATA);
        if ($xml === false) {
            throw new \RuntimeException("Failed to parse XML output from command: $command");
        }
        // Convert XML to an associative array
        $json = json_encode($xml);
        $data = json_decode($json, true);
        $data = $this->normalizeArray($data);
        return $this->filterData($data);
    }
}