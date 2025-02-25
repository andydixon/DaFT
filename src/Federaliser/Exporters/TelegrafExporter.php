<?php

namespace Federaliser\Exporters;

use RuntimeException;

/**
 * Class TelegrafExporter
 *
 * Converts multidimensional arrays into a single JSON object suitable for Telegraf.
 * The first column becomes the key, and the second column becomes the value.
 */
class TelegrafExporter extends AbstractExporter
{
    /**
     * Outputs a Telegraf-compatible JSON response.
     *
     * @param array $data           Multidimensional array of data.
     * @param int   $statusCode     HTTP status code.
     * @param array $additionalConfig Additional configuration.
     * @throws RuntimeException     If there are more than two columns.
     */
    public static function export(array $data, int $statusCode = 200, $additionalConfig = []): void
    {
        header('Content-Type: application/json; charset=utf-8', true, $statusCode);

        $telegrafData = [];

        foreach ($data as $row) {
            if (count($row) > 2) {
                // Too many columns - Telegraf only supports key-value pairs
                http_response_code(400);
                header('Content-Type: application/json');
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Telegraf only supports key-value pairs. Too many columns in data.',
                    'details' => 'Each row must have exactly 2 columns: the first as the key and the second as the value.'
                ]);
                return;
            }

            $keys = array_keys($row);
            $key = $row[$keys[0]];
            $value = $row[$keys[1]];

            // If the value isn't numeric, throw an error
            if (!is_numeric($value)) {
                http_response_code(400);
                echo json_encode([
                    'status' => 'error',
                    'message' => 'Telegraf expects numeric values. Found non-numeric value.',
                    'details' => [
                        'key' => $key,
                        'value' => $value
                    ]
                ]);
                return;
            }

            $telegrafData[$key] = (float) $value;
        }

        echo json_encode($telegrafData, JSON_NUMERIC_CHECK);
    }
}
