<?php

namespace DaFT\Dataformats;

use PDO;
use PDOException;
use DaFT\Logger;

/**
 * Class ExpectationalMySQLHandler
 *
 * This handler extends GenericHandler and adds "expectational completion" logic on MySQL queries.
 * 
 * It executes a provided SQL query and ensures that for every distinct combination of key fields 
 * (defined by `KeyField` config), all possible expected values (defined by `ExpectedValues` config) 
 * for a particular field (`ExpectationField`) are present in the final output.
 * 
 * If any expected values are missing for a particular key group, synthetic rows will be created with:
 * - KeyField values set according to the group.
 * - ExpectationField set to the missing expected value.
 * - ValueField set to DefaultExpectedValue (defaults to 0 if not provided).
 * - Other fields copied from the first row of the group where possible.
 * 
 * Configuration Options:
 * 
 * - source (string): MySQL host/IP address.
 * - port (int): MySQL port. Defaults to 3306.
 * - default_db (string): MySQL database name.
 * - username (string): MySQL username.
 * - password (string): MySQL password.
 * - query (string): The SQL query to execute. Can be an inline query or a file path using `<<` notation.
 * - params (array): Optional query parameters.
 * - KeyField (string): Comma-separated list of column names to group data on. Can be single or multiple fields.
 * - ExpectationField (string): The column whose values are validated against ExpectedValues.
 * - ExpectedValues (string): Comma-separated list of all valid/expected values for ExpectationField.
 * - DefaultExpectedValue (mixed): Value to assign to ValueField when a synthetic row is created. Defaults to 0.
 * - ValueField (string): The column that holds the actual numeric or scalar value to be set when creating synthetic rows.
 * 
 * Limitations:
 * - Only existing key combinations returned by the SQL query are processed. Missing combinations are not generated.
 * - No sorting applied to the output other than placing ValueField as the last field.
 */
class ExpectationalMySQLHandler extends GenericHandler
{
    /**
     * @var string Directory for replication lock files (inherited from GenericHandler structure)
     */
    protected $lockfileDir = __DIR__ . '/../../../mysql_replication_locks';

    /**
     * Main handler entry point.
     * 
     * Executes the configured MySQL query, processes the result set and inserts synthetic rows
     * where expectations are not met.
     * 
     * @return array The fully expanded result set containing both actual and synthetic rows.
     */
    public function handle(): array
    {
        $host   = $this->config['source'] ?? '';
        $port   = $this->config['port'] ?? 3306;
        $dbName = $this->config['default_db'] ?? '';
        $user   = $this->config['username'] ?? '';
        $pass   = $this->config['password'] ?? '';
        $sql    = parent::parseSQL($this->config['query'] ?? '');
        $params = $this->config['params'] ?? [];

        $keyFields = array_map('trim', explode(',', $this->config['KeyField'] ?? ''));
        $expectationField = $this->config['ExpectationField'] ?? '';
        $expectedValues = array_map('trim', explode(',', $this->config['ExpectedValues'] ?? ''));
        $defaultExpectedValue = $this->config['DefaultExpectedValue'] ?? 0;
        $valueField = $this->config['ValueField'] ?? null;
        $expected = [];

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $data = $stmt->fetchAll();

            // Group data by composite keyFields
            $grouped = [];
            foreach ($data as $row) {
                $keyParts = [];
                foreach ($keyFields as $field) {
                    $keyParts[] = $row[$field] ?? '__MISSING_KEY__';
                }
                $compositeKey = implode('||', $keyParts);
                if (!isset($grouped[$compositeKey])) {
                    $grouped[$compositeKey] = [
                        'rows' => [],
                        'keyValues' => []
                    ];
                    foreach ($keyFields as $field) {
                        $grouped[$compositeKey]['keyValues'][$field] = $row[$field] ?? null;
                    }
                }
                $grouped[$compositeKey]['rows'][] = $row;
            }

            $finalData = [];
            foreach ($grouped as $group) {
                $rows = $group['rows'];
                $existingValues = array_column($rows, $expectationField);

                // Preserve original rows exactly
                foreach ($rows as $row) {
                    $orderedRow = [];
                    foreach ($row as $field => $value) {
                        if ($field !== $valueField) {
                            $orderedRow[$field] = $value;
                        }
                    }
                    $orderedRow[$valueField] = $row[$valueField] ?? (is_numeric($defaultExpectedValue) ? (float)$defaultExpectedValue : $defaultExpectedValue);
                    $finalData[] = $orderedRow;
                }

                header("x-debug-preadd: " . count($finalData) . " - Entering missing expectations code");

                // Add missing expectations
                foreach ($expectedValues as $expectedValue) {
                    if (!in_array($expectedValue, $existingValues)) {
                        $newRow = $group['keyValues'];
                        $newRow[$expectationField] = $expectedValue;

                        // Use any other available fields from first row
                        $templateRow = (!empty($rows)) ? $rows[0] : [];
                        foreach ($templateRow as $field => $value) {
                            if (!isset($newRow[$field]) && $field !== $valueField && $field !== $expectationField) {
                                $newRow[$field] = $value;
                            }
                        }

                        $newRow[$valueField] = is_numeric($defaultExpectedValue) ? (float)$defaultExpectedValue : $defaultExpectedValue;

                        $orderedNewRow = [];
                        foreach ($newRow as $field => $value) {
                            if ($field !== $valueField) {
                                $orderedNewRow[$field] = $value;
                            }
                        }
                        $orderedNewRow[$valueField] = $newRow[$valueField];
                        $expected[] = $orderedNewRow;
                    }
                }
            }
            $final = array_merge($expected, $finalData);
            return $final;
        } catch (PDOException $ex) {
            Logger::logPDOException($ex, $sql, $params);
            return [
                'error' => $ex->getMessage(),
                'query' => $sql,
                'params' => $this->config
            ];
        }
    }
}
