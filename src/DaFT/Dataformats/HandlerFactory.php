<?php
namespace DaFT\Dataformats;

use InvalidArgumentException;
use DaFT\Factory\DynamicExtendableFactory;

/**
 * Class HandlerFactory
 * 
 * Responsible for creating an instance of the appropriate handler 
 * based on the 'type' specified in the configuration.
 * 
 * This factory pattern provides a centralised way to instantiate 
 * different data format handlers, ensuring consistency and scalability.
 * 
 *  
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Dataformats
 */
class HandlerFactory extends DynamicExtendableFactory
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
        $type = parent::resolveClass('\\DaFT\\Dataformats\\', 'Handler', 'Generic', $config['type']);
        return new $type($config);
    }
}