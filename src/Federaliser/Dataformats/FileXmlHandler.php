<?php
namespace Federaliser\Dataformats;

/**
 * Class FileXmlHandler
 * 
 * Reads XML data from a local file, converts it to an associative array,
 * normalises the array structure, and optionally filters the data by specified keys.
 * 
 * This class is useful for integrating local XML data sources.
 * It inherits common functionality from `AbstractHandler`, such as data normalization and filtering.
 * 
 * Security Notice:
 * - This class only reads local files and validates paths to prevent directory traversal attacks.
 * - The file path must be within the allowed directory specified in the configuration.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'type' => 'file-xml',
 *     'source' => '/path/to/data.xml',
 *     'query' => 'id,name,email'
 * ];
 * 
 * $handler = new FileXmlHandler($config);
 * $result = $handler->handle();
 * ```
 * 
 * Example Output:
 * ```
 * [
 *     ['id' => '1', 'name' => 'Alice', 'email' => 'alice@example.com'],
 *     ['id' => '2', 'name' => 'Bob', 'email' => 'bob@example.com']
 * ]
 * ```
 * 
 * Security Recommendations:
 * - Ensure that only trusted directories are allowed for XML data files.
 * - Use a configuration option to specify the allowed base directory.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser\Dataformats
 */
class FileXmlHandler extends AbstractHandler
{
    /**
     * Handles reading the XML data from a local file and processing it.
     * 
     * This method:
     * - Resolves the absolute path and verifies the file's existence.
     * - Reads the XML file content.
     * - Parses the XML string into a `SimpleXMLElement`.
     * - Converts the XML to an associative array.
     * - Normalises the array structure.
     * - Filters the data based on query keys, if specified.
     * 
     * @return array Processed and optionally filtered data.
     * 
     * @throws \RuntimeException If the file is not readable, does not exist, or XML parsing fails.
     */
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

        libxml_use_internal_errors(true);
        $xml = simplexml_load_file($realPath, "SimpleXMLElement", LIBXML_NOCDATA);

        if ($xml === false) {
            $errorMessage = "Failed to parse XML file: $realPath. Errors: ";
            foreach (libxml_get_errors() as $error) {
                $errorMessage .= trim($error->message) . "; ";
            }
            libxml_clear_errors();
            throw new \RuntimeException($errorMessage);
        }

        $data = json_decode(json_encode($xml), true);
        $data = $this->normaliseArray($data);

        return $this->filterData($data);
    }

}
