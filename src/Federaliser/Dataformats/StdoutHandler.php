<?php
namespace Federaliser\Dataformats;
/**
 * Class StdoutHandler
 * Executes an external application (given in hostname) and processes its STDOUT.
 * If a query is set, it is assumed to be a regex pattern with named capturing groups.
 */
class StdoutHandler extends AbstractHandler
{
    public function handle(): array
    {
        $command = $this->config['hostname'] ?? '';
        $output = shell_exec($command);
        if ($output === null) {
            throw new \RuntimeException("Command execution failed: $command");
        }
        if (isset($this->config['query']) && !empty($this->config['query'])) {
            $pattern = $this->config['query'];
            $matches = [];
            if (preg_match_all($pattern, $output, $matches, PREG_SET_ORDER)) {
                // Each $match will have numeric keys as well as named keys if provided in the regex.
                $results = [];
                foreach ($matches as $match) {
                    $entry = [];
                    foreach ($match as $key => $value) {
                        if (!is_int($key)) { // Only include named groups.
                            $entry[$key] = $value;
                        }
                    }
                    $results[] = $entry;
                }
                return $results;
            }
            return [];
        }
        return [$output];
    }
}