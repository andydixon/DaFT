<?php
namespace Federaliser\Dataformats;
/**
 * Class AppJsonHandler
 * Executes a command (specified in hostname) that outputs JSON, decodes it, normalizes it and optionally filters by keys.
 */
class AppJsonHandler extends AbstractHandler
{
    public function handle(): array
    {
        $command = $this->config['hostname'] ?? '';
        $output = shell_exec($command);
        if ($output === null) {
            throw new \RuntimeException("Command execution failed: $command");
        }
        $data = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decode error: " . json_last_error_msg());
        }
        $data = $this->normalizeArray($data);
        return $this->filterData($data);
    }
}