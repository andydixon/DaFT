<?php
/**
 * Federaliser Configuration Parser Class
 * 
 * This class is responsible for loading and parsing a configuration file in INI format.
 * It supports parsing with sections (`parse_ini_file` with `sections=true`) and raw scanning mode.
 * 
 * Usage Example:
 * ```
 * $configParser = new ConfigParser('/path/to/config.ini');
 * $config = $configParser->getConfig();
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser
 */

namespace Federaliser;

use RuntimeException;

class ConfigParser
{
    /**
     * @var string Path to the configuration file.
     */
    private string $filepath;

    /**
     * @var array Parsed configuration data.
     */
    private array $config = [];

    /**
     * ConfigParser constructor.
     * 
     * Initialises the configuration parser by:
     * - Setting the file path.
     * - Automatically parsing the configuration file upon instantiation.
     * 
     * @param string $filepath Path to the configuration file.
     * 
     * @throws RuntimeException If the file does not exist or cannot be parsed.
     */
    public function __construct(string $filepath)
    {
        $this->filepath = $filepath;
        $this->parseConfig();
    }

    /**
     * Parses the configuration file using `parse_ini_file()` with sections and raw scanning mode.
     * 
     * This method:
     * - Checks if the file exists before attempting to parse.
     * - Parses the INI file into an associative array.
     * - Uses `INI_SCANNER_RAW` to prevent unwanted changes (e.g., numeric conversions).
     * - Stores the parsed configuration in `$this->config`.
     * 
     * Error Handling:
     * - Throws `RuntimeException` if the file is not found or if parsing fails.
     * 
     * @return void
     * 
     * @throws RuntimeException If the file is not found or parsing fails.
     */
    private function parseConfig(): void
    {
        // Check if the configuration file exists
        if (!file_exists($this->filepath)) {
            throw new RuntimeException("Config file not found: {$this->filepath}");
        }

        // Parse the INI file with sections and raw scanner mode
        $parsed = parse_ini_file($this->filepath, true, INI_SCANNER_RAW);

        // Check if parsing was successful
        if ($parsed === false) {
            throw new RuntimeException("Failed to parse INI file: {$this->filepath}");
        }

        // Store the parsed configuration
        $this->config = $parsed;
    }

    /**
     * Retrieves the parsed configuration as an associative array.
     * 
     * This method returns the parsed configuration in the following format:
     * ```
     * [
     *     'section1' => [
     *         'identifier' => 'value1',
     *         'hostname' => 'value2',
     *      ...
     *     ],
     *     'section2' => [
     *         'identifier' => 'valueA',
     *         'hostname' => 'valueB',
     *      ...
     *     ]
     * ]
     * ```
     * 
     * @return array Parsed configuration data.
     */
    public function getConfig(): array
    {
        return $this->config;
    }
}
