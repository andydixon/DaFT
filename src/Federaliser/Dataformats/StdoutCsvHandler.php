<?php
namespace Federaliser\Dataformats;

class StdoutCsvHandler extends AbstractHandler
{
    use CsvParserTrait;
    
    public function handle(): array
    {
        $command = $this->config['source'] ?? '';
        $output = shell_exec($command);
        if ($output === null) {
            throw new \RuntimeException("Unable to execute command: $command");
        }
        
        $data = $this->parseCsv($output);
        $data = $this->normalizeArray($data);
        return $this->filterData($data);
    }
}
