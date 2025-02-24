<?php
namespace Federaliser\Dataformats;
/**
 * Class AppXmlHandler
 * Executes a command (specified in source) that outputs XML, converts it to an array and optionally filters by keys.
 */
class AppXmlHandler extends AbstractHandler
{
    public function handle(): array
    {
        $command = $this->config['source'] ?? '';
        $output = shell_exec($command);
        if ($output === null) {
            throw new \RuntimeException("Command execution failed: $command");
        }
        $xml = @simplexml_load_string($output, "SimpleXMLElement", LIBXML_NOCDATA);
        if ($xml === false) {
            throw new \RuntimeException("Failed to parse XML output from command: $command");
        }
        $json = json_encode($xml);
        $data = json_decode($json, true);
        $data = $this->normalizeArray($data);
        return $this->filterData($data);
    }
}