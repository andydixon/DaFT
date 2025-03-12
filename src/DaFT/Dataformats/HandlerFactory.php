<?php
namespace DaFT\Dataformats;

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
 * @todo consolidate code from HandlerFactory and ExporterFactory into a generic Factory class and both extend from that
 *  
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Dataformats
 */
class HandlerFactory
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
     * @param array $config Configuration array containing at least a 'type' key.
     * 
     * @return DataFormatHandlerInterface An instance of the appropriate handler, all fallback to the badly named GenericHandler.
     * 
     */
    public static function create(array $config): DataFormatHandlerInterface
    {
        $type = self::resolveDataformatHandler($config['type'] ?? 'generic');
        return new $type($config);

    }
    /**
     * Identify if a specific handler exists for the given type.
     * @param string $type
     * @return string
     */
    private static function resolveDataformatHandler(string $type): string
    {
        $type = strtolower($type);
        // Remove any hyphen and uppercase the immediately following letter
        $type = preg_replace_callback(
            '/-(\w)/',
            fn($m) => strtoupper($m[1]),
            $type
        );

        $type = ucfirst($type);
        $className = $type . 'Handler';
        $fqcn = '\\Federator\\Dataformats\\' . $className;

        // Check via Composer's autoloader
        // If the class doesn’t exist, use "GenericHandler"
        if (!class_exists($fqcn)) {
            $className = 'GenericHandler';
        }

        return $className;
    }
}