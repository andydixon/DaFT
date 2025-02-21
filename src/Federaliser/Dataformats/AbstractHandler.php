<?php
namespace Federaliser\Dataformats;

/**
 * Class AbstractHandler
 * Provides common functionality like normalizing arrays and filtering results by query keys.
 */
abstract class AbstractHandler implements DataFormatHandlerInterface
{
    /**
     * @var array The configuration array.
     */
    protected $config;

    /**
     * Constructor.
     *
     * @param array $config Configuration with keys such as hostname, type, query, etc.
     */
    public function __construct(array $config)
    {
        $this->config = $config;
    }

    /**
     * If query is set (as a comma‐separated list), returns an array of keys.
     * For stdout handlers the query is expected to be a regex pattern and is handled differently.
     *
     * @return array|null
     */
    protected function getQueryKeys(): ?array
    {
        if (isset($this->config['query']) && !empty($this->config['query']) && ! $this->isRegexQuery()) {
            // Comma separated keys (e.g. "id,name,email")
            return array_map('trim', explode(',', $this->config['query']));
        }
        return null;
    }

    /**
     * Determine if the query should be treated as a regex.
     *
     * @return bool
     */
    protected function isRegexQuery(): bool
    {
        // For the stdout type, we assume the query is a regex.
        return isset($this->config['type']) && $this->config['type'] === 'stdout';
    }

    /**
     * Ensures that the data is a multidimensional array.
     * If $data is a single (associative) array, it will be wrapped in an outer array.
     *
     * @param mixed $data
     * @return array
     */
    protected function normalizeArray($data): array
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
     * Filters each element of the data array to only include keys specified in the query.
     *
     * @param array $data
     * @return array
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