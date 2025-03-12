<?php
namespace DaFT\Exporters;

use InvalidArgumentException;

/**
 * Class HandlerFactory
 * 
 * Responsible for creating an instance of the appropriate handler 
 * based on the 'type' specified in the configuration.
 * 
 * This factory pattern provides a centralised way to instantiate 
 * different data format handlers, ensuring consistency and scalability.
 *  
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Dataformats
 */
class ExporterFactory
{
    /**
     * Create a handler instance based on the 'type' in the configuration.
     * 
     * Example:
     * ```
     * $config = ['type' => 'web-json', 'source' => 'https://example.com/data.json'];
     * $handler = HandlerFactory::create($config);
     * $data = $handler->handle();
     * ```
     * 
     * This *should* allow for new data formats to be added without changing any code
     * unlike previous implementations where the handler was instantiated directly from
     * a switch statement.
     * 
     * @todo consolidate code from HandlerFactory and ExporterFactory into a generic Factory class and both extend from that
     * 
     * @param array $config Configuration array containing at least a 'type' key.
     * 
     * @return Exporter An instance of the appropriate handler, all fallback to the badly named JsonExporter.
     * 
     */
    public static function create(string $exporter='JSON'): GenericExporter
    {
        $type = self::resolveExporter($config['type'] ?? 'Json');
        return new $type($exporter);

    }
    /**
     * Identify if a specific handler exists for the given type.
     * @param string $type 
     * @return string
     */
    private static function resolveExporter(string $type): string
    {
        $type = strtolower($type);
        // Remove any hyphen and uppercase the immediately following letter
        $type = preg_replace_callback(
            '/-(\w)/',
            fn($m) => strtoupper($m[1]),
            $type
        );

        $type = ucfirst($type);
        $className = $type . 'Exporter';
        $fqcn = '\\Federator\\Exporters\\' . $className;

        // Check via Composer's autoloader
        // If the class doesn’t exist, use "JsonExporter"
        if (!class_exists($fqcn)) {
            $className = 'JsonExporter';
        }

        return $className;
    }
}