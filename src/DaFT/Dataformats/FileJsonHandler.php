<?php
namespace DaFT\Dataformats;

/**
 * Class FileJsonHandler
 * 
 * Reads JSON data from a local file, decodes it into an associative array,
 * normalises the array structure, and optionally filters the data by specified keys.
 * 
 * Security Notice:
 * - This class only reads local files and validates paths to prevent directory traversal attacks.
 * - The file path must be within the allowed directory specified in the configuration.
 * 
 * @namespace DaFT\Dataformats
 */
class FileJsonHandler extends AbstractHandler
{
    public function handle(): array
    {
        $file = $this->config['source'] ?? '';
        $realPath = realpath($file);

        if ($realPath === false ) {
            throw new \RuntimeException("Invalid or unauthorised file path: $file");
        }

        if (!is_readable($realPath)) {
            throw new \RuntimeException("Unable to read file: $realPath");
        }

        $json = file_get_contents($realPath);
        $data = json_decode($json, true);

        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \RuntimeException("JSON decode error: " . json_last_error_msg());
        }

        $data = $this->normaliseArray($data);

        return $this->filterData($data);
    }

}
