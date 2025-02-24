<?php
namespace Federaliser\Dataformats;

class WebCsvHandler extends AbstractHandler
{
    use CsvParserTrait;
    
    public function handle(): array
    {
        $url = $this->config['source'] ?? '';
        $csv = @file_get_contents($url);
        if ($csv === false) {
            throw new \RuntimeException("Unable to fetch URL: $url");
        }
        
        $data = $this->parseCsv($csv);
        $data = $this->normalizeArray($data);
        return $this->filterData($data);
    }
}
