<?php
namespace DaFT\Factory;

use stdClass;

abstract class DynamicExtendableFactory
{
    /**
     * Shared code between Exporter and Dataformat (Handler) factories.
     * Allows dynamic instantiation of classes based on the 'type' and namespace.
     * Should help sort out messy case statements in places.
     * @todo document this properly. It's 2am and I'm tired but cant sleep. Thanks, meds.
     */

    /**
     * Identify if a specific handler exists for the given type.
     * @param string $type 
     * @return string
     */
    static function resolveClass(string $namespace='\\Federator\\', string $suffix='', string $generic='generic', string $type): string
    {
        $type = strtolower($type);
        // Remove any hyphen and uppercase the immediately following letter
        $type = preg_replace_callback(
            '/-(\w)/',
            fn($m) => strtoupper($m[1]),
            $type
        );

        $type = ucfirst($type);
        $className = $type . ucfirst($suffix);
        $fqcn = $namespace . $className;

        // Check via Composer's autoloader
        // If the class doesn’t exist, use "JsonExporter"
        if (!class_exists($fqcn)) {
            $className = $namespace.ucfirst($generic) . ucfirst($suffix);
        }

        return $fqcn;
    }
}