<?php
/**
 * Federaliser Configuration Model Class
 * 
 * This class manages the configuration data stored in an INI file.
 * It supports CRUD operations on sections and key-value pairs, ensuring data integrity and validation.
 * 
 * Features:
 * - Automatically creates a blank config file if it does not exist.
 * - Parses INI files with sections (`parse_ini_file` with `sections=true`).
 * - Validates configuration data, including unique identifiers and allowed types.
 * - Provides methods to get, create, update, and delete sections.
 * - Writes configuration changes back to the `.ini` file.
 * 
 * Usage Example:
 * ```
 * $configModel = new ConfigModel('/path/to/config.ini');
 * 
 * // Get all sections
 * $allSections = $configModel->getAll();
 * 
 * // Get a single section
 * $sectionData = $configModel->get('example_section');
 * 
 * // Create a new section
 * $configModel->create('new_section', [
 *     'type' => 'mysql',
 *     'identifier' => 'example'
 * ]);
 * 
 * // Update an existing section
 * $configModel->update('existing_section', 'renamed_section', [
 *     'type' => 'redshift',
 *     'identifier' => 'new_example'
 * ]);
 * 
 * // Delete a section
 * $configModel->delete('obsolete_section');
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser\Config
 */

namespace Federaliser\Config;

use Exception;

class ConfigModel
{
    /**
     * @var string Absolute path to the configuration file.
     */
    private string $configFile;

    /**
     * @var array Parsed configuration data as an associative array.
     */
    private array $configData = [];

    /**
     * ConfigModel constructor.
     * 
     * Initialises the configuration model by:
     * - Setting the file path.
     * - Checking if the file exists or creating a blank one.
     * - Parsing the configuration file into `$configData`.
     * 
     * @param string $configFile Absolute path to config.ini.
     * 
     * @throws \Exception If the file cannot be created or parsed.
     */
    public function __construct(string $configFile)
    {
        $this->configFile = $configFile;

        // Check if the config file exists, otherwise create a blank one
        if (!file_exists($configFile)) {
            if (@file_put_contents($configFile, "") === false) {
                throw new Exception("Config file does not exist and a blank one cannot be created: {$configFile}");
            }
        }

        // Parse the INI file with sections and raw scanning mode
        $parsedData = parse_ini_file($configFile, true, INI_SCANNER_RAW);

        // If the file is empty, set configData as an empty array
        $this->configData = ($parsedData === false) ? [] : $parsedData;
    }

    /**
     * Retrieve all sections in config.ini.
     * 
     * @return array Associative array of all configuration sections.
     */
    public function getAll(): array
    {
        return $this->configData;
    }

    /**
     * Retrieve a single section by name.
     * 
     * @param string $section Name of the section to retrieve.
     * 
     * @return array|null Array of key-value pairs in the section, or null if the section does not exist.
     */
    public function get(string $section): ?array
    {
        return $this->configData[$section] ?? null;
    }

    /**
     * Create a new section.
     * 
     * - Validates the data.
     * - Checks if the section already exists.
     * - Ensures the identifier is unique.
     * - Writes the updated configuration to the file.
     * 
     * @param string $section Name of the section (text in [brackets]).
     * @param array  $data    Key-value pairs for that section.
     * 
     * @throws \Exception If the section already exists or if validation fails.
     */
    public function create(string $section, array $data): void
    {
        $this->validateData($data);

        if (isset($this->configData[$section])) {
            throw new Exception("Section already exists: {$section}");
        }

        $this->checkUniqueIdentifier($data['identifier']);

        $this->configData[$section] = $data;
        $this->write();
    }

    /**
     * Validate the data:
     * - 'type' must be one of (mysql, mssql, redshift, prometheus).
     * - 'identifier' must be lowercase.
     * 
     * @param array $data Key-value pairs to validate.
     * 
     * @throws \Exception If validation fails.
     */
    private function validateData(array $data): void
    {
        $validTypes = ['mysql', 'mssql', 'redshift', 'prometheus'];

        if (!isset($data['type']) || !in_array($data['type'], $validTypes, true)) {
            throw new Exception("Invalid type. Must be one of: ".implode(', ', $validTypes).".");
        }

        if (!isset($data['identifier']) || $data['identifier'] !== strtolower($data['identifier'])) {
            throw new Exception("Identifier must be lowercase.");
        }
    }

    /**
     * Ensure the identifier is unique across all sections.
     * 
     * @param string $identifier Identifier to check for uniqueness.
     * 
     * @throws \Exception If the identifier is not unique.
     */
    private function checkUniqueIdentifier(string $identifier): void
    {
        foreach ($this->configData as $sectionData) {
            if (isset($sectionData['identifier']) && $sectionData['identifier'] === $identifier) {
                throw new Exception("Identifier '{$identifier}' is already used in another section.");
            }
        }
    }

    /**
     * Writes the configuration data back to the .ini file.
     * 
     * - Constructs the .ini file content from the `$configData` array.
     * - Each section is written with its key-value pairs.
     * - Ensures consistent formatting and error handling.
     * 
     * @throws \Exception If the file cannot be written.
     */
    private function write(): void
    {
        $content = '';

        foreach ($this->configData as $section => $values) {
            $content .= "[{$section}]\n";
            foreach ($values as $key => $val) {
                // Escape any special characters
                $escapedVal = addslashes($val);
                $content .= "{$key} = \"{$escapedVal}\"\n";
            }
            $content .= "\n";
        }

        if (file_put_contents($this->configFile, $content) === false) {
            throw new Exception("Failed to write to config file: {$this->configFile}");
        }
    }
}
