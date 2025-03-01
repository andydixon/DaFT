<?php

namespace Federaliser\Dataformats;

/**
 * Class AppJsonHandler
 *
 * Executes a command that outputs JSON, processes it, and returns structured results.
 * Supports:
 * - Extracting a specific path from JSON (using dot notation).
 * - Filtering specific fields from the extracted data.
 */
class AppJsonHandler extends AbstractJsonHandler
{
    /**
     * Executes a command that outputs JSON, processes it, and returns structured output.
     *
     * @return array Processed JSON output.
     * @throws \RuntimeException If the command execution fails or JSON decoding fails.
     */
    public function handle(): array
    {
        $command = $this->config['source'] ?? '';

        // Execute the command and capture its JSON output
        $output = shell_exec($command);
        if ($output === null) {
            throw new \RuntimeException("Command execution failed: $command");
        }

        // Decode JSON response
        $data = json_decode($output, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decode error: " . json_last_error_msg());
        }

        // Process JSON data (extract path, filter fields, normalize)
        return $this->processJsonData($data);
    }
}
