<?php
namespace Federaliser\Dataformats;

trait CsvParserTrait
{
    /**
     * Parses CSV content into an associative array using the first row as header.
     *
     * @param string $csv The CSV content as a string.
     * @return array Parsed CSV data as an array of associative arrays.
     */
    protected function parseCsv(string $csv): array
    {
        // Split CSV into lines, handling various line endings.
        $lines = preg_split('/\r\n|\n|\r/', $csv);
        // Remove any empty lines.
        $lines = array_filter($lines, function ($line) {
            return trim($line) !== '';
        });
        
        $data = [];
        if (empty($lines)) {
            return $data;
        }
        
        // The first non-empty line contains the headers.
        $header = str_getcsv(array_shift($lines));
        
        // Parse each line and combine with header to form an associative array.
        foreach ($lines as $line) {
            $row = str_getcsv($line);
            if (count($row) === count($header)) {
                $data[] = array_combine($header, $row);
            }
        }
        
        return $data;
    }
}
