<?php
namespace DaFT\Dataformats;

/**
 * Class GenericHandler
 * 
 * Provides common functionality for data handlers including:
 * - Normalizing arrays to ensure a consistent multidimensional structure.
 * - Filtering results based on query keys.
 * - Handling query keys as either a comma-separated list or a regex pattern.
 * 
 * This abstract class is meant to be extended by specific data handlers 
 * that implement the DataFormatHandlerInterface.
 * 
 * Usage Example (in a child class):
 * ```
 * class ExampleHandler extends GenericHandler {
 *     public function handle() {
 *         $data = [
 *             ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
 *             ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com']
 *         ];
 *         $normalised = $this->normaliseArray($data);
 *         $filtered = $this->filterData($normalised);
 *         return $filtered;
 *     }
 * }
 * 
 * $handler = new ExampleHandler(['query' => 'id,name']);
 * $result = $handler->handle();
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Dataformats
 */
abstract class GenericHandler implements DataFormatHandlerInterface
{
    /**
     * @var array Configuration array with keys such as 'source', 'type', 'query', etc.
     */
    protected array $config;

    /**
     * Constructor.
     * 
     * Initialises the handler with the provided configuration.
     * 
     * Example:
     * ```
     * $config = [
     *     'source' => 'database',
     *     'type' => 'mysql',
     *     'query' => 'id,name,email'
     * ];
     * $handler = new ExampleHandler($config);
     * ```
     * 
     * @param array $config Configuration array with keys such as 'source', 'type', 'query', etc.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * Retrieves query keys as an array.
     * 
     * - If 'query' is set as a comma-separated list, it returns an array of keys.
     * - If the 'type' is set to 'stdout', the query is assumed to be a regex pattern.
     * - If no query is set, or if the query is a regex, it returns null.
     * 
     * Example:
     * ```
     * $config = ['query' => 'id,name,email'];
     * $keys = $this->getQueryKeys(); // ['id', 'name', 'email']
     * ```
     * 
     * @return array|null Array of query keys or null if not applicable.
     */
    protected function getQueryKeys(): ?array
    {
        if (!(isset($this->config['type']) && str_contains($this->config['type'],'sql')) &&
            isset($this->config['query']) && !empty($this->config['query']) && !$this->isRegexQuery()) {
            // Split comma-separated keys and trim whitespace
            return array_map('trim', explode(',', $this->config['query']));
        }
        return null;
    }

    /**
     * Determines if the query should be treated as a regex pattern.
     * 
     * For handlers with 'type' set to 'stdout', the query is expected to be a regex.
     * 
     * Example:
     * ```
     * $config = ['type' => 'stdout', 'query' => '/id|name/'];
     * $isRegex = $this->isRegexQuery(); // true
     * ```
     * 
     * @return bool True if the query is a regex, false otherwise.
     */
    protected function isRegexQuery(): bool
    {
        return isset($this->config['type']) && $this->config['type'] === 'stdout';
    }

    /**
     * Normalises data to a multidimensional array.
     * 
     * - If $data is a single (associative) array, it wraps it in an outer array.
     * - If the first element is not an array, it wraps the entire array.
     * - If $data is not an array, it returns an empty array.
     * 
     * Example:
     * ```
     * $data = ['id' => 1, 'name' => 'Alice'];
     * $normalised = $this->normaliseArray($data); 
     * // Result: [['id' => 1, 'name' => 'Alice']]
     * ```
     * 
     * @param mixed $data The input data to normalise.
     * 
     * @return array A normalised multidimensional array.
     */
    protected function normaliseArray($data): array
    {
        if (!is_array($data)) {
            return [];
        }

        // Check if the first element is an array; if not, wrap the whole array.
        $first = reset($data);
        if (!is_array($first)) {
            return [$data];
        }
        return $data;
    }

    /**
     * Filters data based on the query keys.
     * 
     * - Only includes keys specified in the query.
     * - If no query is set, the data is returned as-is.
     * 
     * Example:
     * ```
     * $data = [
     *     ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
     *     ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com']
     * ];
     * 
     * $config = ['query' => 'id,name'];
     * $filtered = $this->filterData($data);
     * 
     * // Result:
     * // [
     * //     ['id' => 1, 'name' => 'Alice'],
     * //     ['id' => 2, 'name' => 'Bob']
     * // ]
     * ```
     * 
     * @param array $data The input data array.
     * 
     * @return array Filtered data including only the queried keys.
     */
    protected function filterData(array $data): array
    {
        $keys = $this->getQueryKeys();
        if (!$keys) {
            return $data;
        }

        $filtered = [];
        foreach ($data as $item) {
            $temp = [];
            foreach ($keys as $key) {
                if (isset($item[$key])) {
                    $temp[$key] = $item[$key];
                }
            }
            $filtered[] = $temp;
        }
        return $filtered;
    }
}
