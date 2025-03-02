<?php

namespace Federaliser\Dataformats;

/**
 * Class AppXmlHandler
 *
 * Executes a command that outputs XML, extracts a path, and filters fields.
 */
class AppXmlHandler extends AbstractXmlHandler
{
    /**
     * Executes a command that outputs XML, processes it, and returns structured output.
     *
     * @return array Processed XML output.
     * @throws \RuntimeException If the command execution fails or XML parsing fails.
     */
    public function handle(): array
    {
        $command = $this->config['source'] ?? '';

        // Execute the command and capture its XML output
        $xmlContent = shell_exec($command);
        if ($xmlContent === null) {
            throw new \RuntimeException("Command execution failed: $command");
        }

        // Parse and process XML
        $data = $this->parseXml($xmlContent);
        return $this->processXmlData($data);
    }
}
