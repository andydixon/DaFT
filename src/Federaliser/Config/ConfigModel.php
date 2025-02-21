<?php

namespace Federaliser\Config;

class ConfigModel
{
    private string $configFile;
    private array $configData = [];

    /**
     * @param string $configFile Absolute path to config.ini
     * @throws \Exception If the config file does not exist and cannot be created,
     *                    or if it cannot be parsed.
     */
    public function __construct(string $configFile)
    {
        $this->configFile = $configFile;

        if (!file_exists($configFile)) {
            // Attempt to create a blank config file.
            if (@file_put_contents($configFile, "") === false) {
                throw new \Exception("Config file does not exist and a blank one cannot be created: {$configFile}");
            }
        }

        $parsedData = parse_ini_file($configFile, true, INI_SCANNER_RAW);
        // If the file is empty, parse_ini_file returns false; in that case, set configData as an empty array.
        $this->configData = ($parsedData === false) ? [] : $parsedData;
    }

    /**
     * Retrieve all sections in config.ini
     *
     * @return array
     */
    public function getAll(): array
    {
        return $this->configData;
    }

    /**
     * Retrieve a single section by name
     *
     * @param string $section
     * @return array|null
     */
    public function get(string $section): ?array
    {
        return $this->configData[$section] ?? null;
    }

    /**
     * Create a new section
     *
     * @param string $section Name of the section (the text in [brackets])
     * @param array  $data    Key-value pairs for that section
     * @throws \Exception
     */
    public function create(string $section, array $data): void
    {
        $this->validateData($data);

        if (isset($this->configData[$section])) {
            throw new \Exception("Section already exists: {$section}");
        }

        $this->checkUniqueIdentifier($data['identifier']);

        $this->configData[$section] = $data;
        $this->write();
    }

    /**
     * Update an existing section
     *
     * @param string $oldSection  Existing section name
     * @param string $newSection  New section name (could be same as old if not renaming)
     * @param array  $data        Key-value pairs for that section
     * @throws \Exception
     */
    public function update(string $oldSection, string $newSection, array $data): void
    {
        if (!isset($this->configData[$oldSection])) {
            throw new \Exception("Section does not exist: {$oldSection}");
        }

        $this->validateData($data);

        // If the identifier changed, check that the new one is unique
        $oldIdentifier = $this->configData[$oldSection]['identifier'] ?? '';
        if ($oldIdentifier !== $data['identifier']) {
            $this->checkUniqueIdentifier($data['identifier']);
        }

        // If the name of the section changes, remove the old and add the new
        if ($oldSection !== $newSection) {
            unset($this->configData[$oldSection]);
        }

        $this->configData[$newSection] = $data;
        $this->write();
    }

    /**
     * Delete an existing section
     *
     * @param string $section
     * @throws \Exception
     */
    public function delete(string $section): void
    {
        if (!isset($this->configData[$section])) {
            throw new \Exception("Section does not exist: {$section}");
        }

        unset($this->configData[$section]);
        $this->write();
    }

    /**
     * Validate the data: 'type' must be one of (mysql, mssql, redshift, prometheus)
     * and 'identifier' must be lowercase
     *
     * @param array $data
     * @throws \Exception
     */
    private function validateData(array $data): void
    {
        $validTypes = ['mysql', 'mssql', 'redshift', 'prometheus'];

        if (!isset($data['type']) || !in_array($data['type'], $validTypes, true)) {
            throw new \Exception(
                "Invalid type. Must be one of: ".implode(', ', $validTypes)."."
            );
        }

        if (!isset($data['identifier'])) {
            throw new \Exception("Missing 'identifier' field.");
        }

        if ($data['identifier'] !== strtolower($data['identifier'])) {
            throw new \Exception("Identifier must be lowercase.");
        }
    }

    /**
     * Ensure identifier is unique across all sections
     *
     * @param string $identifier
     * @throws \Exception
     */
    private function checkUniqueIdentifier(string $identifier): void
    {
        foreach ($this->configData as $sectionData) {
            if (isset($sectionData['identifier']) && $sectionData['identifier'] === $identifier) {
                throw new \Exception("Identifier '{$identifier}' is already used in another section.");
            }
        }
    }

    /**
     * Write the array structure back to the .ini file
     *
     * @throws \Exception
     */
    private function write(): void
    {
        $content = "";

        foreach ($this->configData as $section => $values) {
            $content .= "[{$section}]\n";
            foreach ($values as $key => $val) {
                $content .= $key . " = " . $val . "\n";
            }
            $content .= "\n";
        }

        if (file_put_contents($this->configFile, $content) === false) {
            throw new \Exception("Failed to write to config file: {$this->configFile}");
        }
    }
}
