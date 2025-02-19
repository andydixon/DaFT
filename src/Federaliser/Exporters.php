<?php
/**
 * Federalizer Exporters Class
 * @author Andy Dixon <andy@andydixon.com>
 * @created 2025-01-16
 */
namespace Federaliser;

class Exporters
{
    /**
     * Outputs a JSON response and sets the appropriate headers.
     */
    public static function jsonResponse(array $data, int $statusCode = 200): void
    {
        header('Content-Type: application/json; charset=utf-8', true, $statusCode);
        echo json_encode($data,JSON_NUMERIC_CHECK);
        exit;
    }

    public static function prometheusResponse(string $identifier,array $data, bool $firstColumnIsLabel = false): void
    {
        header("Content-Type: text/plain");

        foreach($data as $row) {

            $keys = array_keys($row);
            $additionalLabels = [];

            // Iterate over the columns, find any that are labels and add them to the labels array
            foreach ($keys as $key) {
                $metricValue = $row[$key];
                $valueType = '';
                $sanitisedKey = '';
                //counter, gauge, histogram, summary, or untyped,
                $prefix = strtolower(substr($key, 0, 4));
                if($prefix=='____') {
                    $additionalLabels[]=array(substr($key, 4) => $metricValue);
                }
            }

            // Iterate over the columns, find any that are metrics and output them, including the additional labels into the openmetrics format
            foreach ($keys as $key) {
              $prefix = strtolower(substr($key, 0, 4));
                if($prefix!='____') {
                    // Concatenate additionalLabels into key="value" string pairs
                    $additionalLabelsString = 'column="$sanitisedKey"';
                    foreach($additionalLabels as $additionalLabel) {
                        $additionalLabelsString .= ',';
                        foreach($additionalLabel as $labelKey => $labelValue) {
                            $additionalLabelsString .= "$labelKey=\"$labelValue\"";
                        }
                    }
        
                    echo "$identifier{$additionalLabelsString} $metricValue\n";
                }

            }
        }
    }
}