<?php
/**
 * DaFT Main Application Class
 * 
 * This is the main entry point for the DaFT application.
 * It is responsible for:
 * - Parsing configuration files.
 * - Initializing the router and defining routes.
 * - Handling requests and delegating them to appropriate handlers.
 * - Exporting the response in Prometheus, OpenMetrics, or JSON formats.
 * 
 * Usage Example:
 * ```
 * $app = new Application('/path/to/config.ini');
 * $app->run();
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT
 */

namespace DaFT;

use DaFT\Dataformats\HandlerFactory;
use DaFT\Exporters\Exporter;
use DaFT\Exporters\ExporterFactory;
use DaFT\Helpers;

class Application
{
    /**
     * @var string Path to the configuration file.
     */
    private string $configFile;

    /**
     * @var array Parsed configuration data.
     */
    private array $configData;

    /**
     * @var Router Instance of the Router for request handling.
     */
    private Router $router;

    /**
     * @var bool Flag to indicate if the request is for Prometheus.
     */
    private bool $isPrometheus = false;

    /**
     * Application constructor.
     * 
     * Initialises the application by:
     * - Loading the configuration file.
     * - Creating the router instance.
     * - Defining the application routes.
     * 
     * @param string $configFilePath Path to the configuration file.
     */
    public function __construct(string $configFilePath)
    {
        $this->configFile = $configFilePath;

        // Parse configuration file
        $configParser = new ConfigParser($this->configFile);
        $this->configData = $configParser->getConfig();

        // Initialise the router
        $this->router = new Router();

        // Define routes based on configuration
        $this->initialiseRoutes();
    }

    /**
     * Initialises routes from the configuration file.
     * 
     * This method:
     * - Loops through each section of the configuration.
     * - Checks for the presence of an 'identifier' key.
     * - Registers routes and defines their handlers.
     * 
     * Each route:
     * - Instantiates the appropriate data handler using `HandlerFactory`.
     * - Processes the request data and catches exceptions.
     * - Exports the response in the requested format (Prometheus, OpenMetrics, or JSON).
     * 
     * @return void
     */
    private function initialiseRoutes(): void
    {
        foreach ($this->configData as $sectionName => $params) {
            // Skip if 'identifier' is not present in the configuration
            if (!isset($params['identifier'])) {
                continue;
            }

            $identifier = $params['identifier'];

            // Register route with the router
            $this->router->add($identifier, function () use ($identifier, $params) {

                // Create the appropriate handler for the data
                $handler = HandlerFactory::create($params);

                // Process the data and handle exceptions
                try {
                    $result = $handler->handle();
                } catch (\Exception $e) {
                    // Capture error message in the result array
                    $result['error'] = ['message' => $e->getMessage()];
                }

                // Check for errors and set HTTP status accordingly
                if (!empty($result['error'])) {
                    header("HTTP/1.1 500 Internal Server Error");
                    header("Content-Type: application/json");
                    die(json_encode($result));
                }

                // Identify the exporter, falling back to JSON

                // Get the cleaned request URI using the Helpers class
                $requestUri = Helpers::cleanUri();

                // Extract the last segment of the URI and convert it to lowercase
                $format = strtolower(basename($requestUri));

                $exporter = ExporterFactory::create($format);

                // Set exporter options
                $exporterOptions = ['identifier' => $identifier];
                
                // Export from the identified exporter (Defaults to JSON)
                $exporter::export($result, 200, $exporterOptions);

            });
        }
    }

    public function getConfigData()
    {
        return $this->configData;
    }

    /**
     * Runs the application by dispatching the request to the router.
     * 
     * This method:
     * - Retrieves the cleaned request URI.
     * - Delegates the request to the router for dispatching.
     * 
     * Example Usage:
     * ```
     * $app->run();
     * ```
     * 
     * @return void
     */
    public function run(): void
    {
        // Get the cleaned request URI
        $requestUri = Helpers::cleanUri(true);

        // Dispatch the request using the router
        $this->router->dispatch($requestUri);
    }
}
