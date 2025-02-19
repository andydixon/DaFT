<?php
/**
 * Federaliser URI routing Management
 * Does the needful on priority
 * @author Andy Dixon <andy@andydixon.com>
 * @created 2025-01-16
 */

namespace Federaliser;

class Router
{
    /**
     * A simple mapping of path -> callback.
     * For real use, replace this with a proper routing library.
     */
    private array $routes = [];

    /**
     * Register a callback for a given path.
     */
    public function add(string $path, callable $callback): void
    {
        // Normalize the path to avoid leading/trailing slashes
        $path = trim($path, '/');
        $this->routes[$path] = $callback;
    }

    /**
     * Find and dispatch the callback for the given path.
     */
    public function dispatch(string $requestPath): void
    {
        if (array_key_exists($requestPath, $this->routes)) {
            call_user_func($this->routes[$requestPath]);
        } else {
            // Simple 404 when there's no endpoint defined in the config
            header('HTTP/1.1 404 Data Source Not Found');

            Exporters::jsonResponse(['error' => 'Not Found','target'=>$requestPath], 404);
        }
    }
}
