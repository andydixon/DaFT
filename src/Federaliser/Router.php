<?php
/**
 * Federaliser URI Routing Management
 * 
 * This class provides a simple URI routing mechanism for the Federaliser application.
 * It maps URI paths to callbacks, allowing dynamic request handling.
  * 
 * Usage Example:
 * ```
 * $router = new Router();
 * $router->add('example/path', function() {
 *     echo "Hello, World!";
 * });
 * 
 * $router->dispatch('example/path');
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser
 */

namespace Federaliser;

use Federaliser\Exporters\JsonExporter;

class Router
{
    /**
     * @var array A mapping of route paths to their corresponding callbacks.
     */
    private array $routes = [];

    /**
     * Registers a callback for a given path.
     * 
     * This method:
     * - Normalises the path by trimming leading and trailing slashes.
     * - Maps the normalised path to the given callback.
     * - Allows dynamic request handling for defined routes.
     * 
     * Example Usage:
     * ```
     * $router->add('example/path', function() {
     *     echo "Example Route";
     * });
     * ```
     * 
     * @param string   $path     The URI path to map to the callback.
     * @param callable $callback The callback function to handle the request.
     * 
     * @return void
     */
    public function add(string $path, callable $callback): void
    {
        // Normalise the path to avoid leading/trailing slashes
        $normalisedPath = trim($path, '/');

        // Map the normalised path to the callback
        $this->routes[$normalisedPath] = $callback;
    }

    /**
     * Dispatches the callback for the given request path.
     * 
     * This method:
     * - Normalises the request path.
     * - Checks if the path is registered in the `$routes` array.
     * - Calls the corresponding callback if found.
     * - Returns a 404 JSON response if no route is defined.
     * 
     * Example Usage:
     * ```
     * $router->dispatch('example/path');
     * ```
     * 
     * Error Handling:
     * - If the route is not defined, it sends a `404 Not Found` response.
     * - Uses `JsonExporter` to standardise the error response format.
     * 
     * Response Format for 404:
     * ```
     * {
     *     "error": "Not Found",
     *     "target": "requested/path"
     * }
     * ```
     * 
     * @param string $requestPath The requested URI path.
     * 
     * @return void
     */
    public function dispatch(string $requestPath): void
    {
        // Normalise the request path
        $normalisedPath = trim($requestPath, '/');

        // Check if the path exists in the route map
        if (array_key_exists($normalisedPath, $this->routes)) {
            // Call the mapped callback
            call_user_func($this->routes[$normalisedPath]);
        } else {
            // Send a 404 response for undefined routes
            header('HTTP/1.1 404 Not Found');
            header('Content-Type: application/json');

            // Standardised JSON response for 404 Not Found
            JsonExporter::export([
                'error' => 'Not Found',
                'target' => $requestPath
            ], 404);
        }
    }
}
