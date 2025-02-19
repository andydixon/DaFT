<?php
/**
 * Federaliser configuration parser
 * @author Andy Dixon <andy@andydixon.com>
 * @created 2025-01-16
 */

namespace Federaliser;

use RuntimeException;

class ConfigParser
{
    private string $filepath;
    private array $config;

    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
        $this->parseConfig();
    }

    private function parseConfig(): void
    {
        if (!file_exists($this->filepath)) {
            throw new RuntimeException("Config file not found: {$this->filepath}");
        }

        // parse_ini_file with sections = true
        $parsed = parse_ini_file($this->filepath, true, INI_SCANNER_RAW);

        if ($parsed === false) {
            throw new RuntimeException("Failed to parse INI file: {$this->filepath}");
        }

        $this->config = $parsed;
    }

    public function getConfig(): array
    {
        return $this->config;
    }
}
