<?php
namespace Federaliser\Dataformats;

class FileCsvHandler extends AbstractHandler
{
    use CsvParserTrait;
    
    public function handle(): array
    {
        $file = $this->config['source'] ?? '';
        if (!is_readable($file)) {
            throw new \RuntimeException("Unable to read file: $file");
        }
        
        $csv = file_get_contents($file);
        if ($csv === false) {
            throw new \RuntimeException("Error reading file: $file");
        }
        
        $data = $this->parseCsv($csv);
        $data = $this->normalizeArray($data);
        return $this->filterData($data);
    }
}
