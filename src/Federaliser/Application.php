<?php
/**
 * Federaliser main application
 * @author Andy Dixon <andy@andydixon.com>
 * @created 2025-01-16
 */

namespace Federaliser;

class Application
{
    private string $configFile;
    private array $configData;
    private Router $router;
    private bool $isPrometheus = false;

    public function __construct(string $configFilePath)
    {
        $this->configFile = $configFilePath;

        // Parse configuration
        $configParser = new ConfigParser($this->configFile);
        $this->configData = $configParser->getConfig();

        // Create our simple router
        $this->router = new Router();

        $this->initializeRoutes();
    }

    /**
     * Map each identifier to a route and define how to handle the request.
     */
    private function initializeRoutes(): void
    {
        foreach ($this->configData as $sectionName => $params) {
            if (!isset($params['identifier'])) {
                continue;
            }

            $identifier = $params['identifier'];
            $isPrometheus = $this->isPrometheus;


            // Register route
            $this->router->add($identifier, function () use ($identifier, $params,$isPrometheus) {
                // Connect to the DB/endpoint
                $connectionFactory = new ConnectionFactory($params);
                $result = $connectionFactory->runQuery();

                if (!empty($result['error'])) {
                    header("HTTP/1.1 500 Something buggered up");
                }

                if(Helpers::isPrometheusExporter()) {
                    Exporters::prometheusResponse($identifier,$result);
                } else {
                    // Output JSON response
                    Exporters::jsonResponse($result);
                }
            });
        }
    }

    /**
     * Runs the application by delegating to the router.
     */
    public function run(): void
    {
        $requestUri = Helpers::cleanUri(true);
        $this->router->dispatch($requestUri);
    }
}
