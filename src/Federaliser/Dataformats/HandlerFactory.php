<?php
namespace Federaliser\Dataformats;

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
 * Features:
 * - Supports a wide range of data formats including JSON, XML, CSV, and databases.
 * - Organises handler instantiation into logical categories for maintainability.
 * - Easily extendable to support new data formats by adding new cases.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'type' => 'web-json',
 *     'source' => 'https://example.com/data.json',
 *     'query' => 'id,name,email'
 * ];
 * 
 * $handler = HandlerFactory::create($config);
 * $data = $handler->handle();
 * ```
 * 
 * Exception Handling:
 * - Throws `InvalidArgumentException` if the specified type is unsupported.
 * 
 * Adding New Handlers:
 * - To add a new handler, follow these steps:
 *     1. Create a new class implementing `DataFormatHandlerInterface`.
 *     2. Place the class under the `Federaliser\Dataformats` namespace.
 *     3. Add a new case in the `create()` method with the appropriate type.
 * 
 * Example of Adding a New Handler:
 * ```
 * case 'new-format':
 *     return new NewFormatHandler($config);
 * ```
 * 
 * Design Considerations:
 * - This class promotes the Open/Closed Principle, allowing easy extension without modification.
 * - It maintains consistent instantiation logic across all data format handlers.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser\Dataformats
 */
class HandlerFactory
{
    /**
     * Create a handler instance based on the 'type' in the configuration.
     * 
     * This method:
     * - Extracts the `type` from the configuration array.
     * - Matches the type against supported handlers using a switch statement.
     * - Instantiates the appropriate handler class and returns it.
     * - Throws `InvalidArgumentException` for unsupported types.
     * 
     * Example:
     * ```
     * $config = ['type' => 'web-json', 'source' => 'https://example.com/data.json'];
     * $handler = HandlerFactory::create($config);
     * $data = $handler->handle();
     * ```
     * 
     * Supported Types:
     * - JSON Handlers: web-json, app-json, file-json
     * - XML Handlers: web-xml, app-xml, file-xml
     * - Database Handlers: mysql, mssql, redshift, prometheus
     * - CSV Handlers: web-csv, file-csv, stdout-csv
     * - Miscellaneous: stdout
     * 
     * Error Handling:
     * - Throws `InvalidArgumentException` if the type is not supported.
     * 
     * @param array $config Configuration array containing at least a 'type' key.
     * 
     * @return DataFormatHandlerInterface An instance of the appropriate handler.
     * 
     * @throws InvalidArgumentException If the type is unsupported.
     */
    public static function create(array $config): DataFormatHandlerInterface
    {
        $type = $config['type'] ?? '';

        switch ($type) {
            /** JSON Handlers */
            case 'web-json':
                return new WebJsonHandler($config);

            case 'app-json':
                return new AppJsonHandler($config);

            case 'file-json':
                return new FileJsonHandler($config);

            /** XML Handlers */
            case 'web-xml':
                return new WebXmlHandler($config);

            case 'app-xml':
                return new AppXmlHandler($config);

            case 'file-xml':
                return new FileXmlHandler($config);

            /** Database Handlers */
            case 'mysql':
                return new MysqlHandler($config);

            case 'mssql':
                return new MssqlHandler($config);

            case 'redshift':
                return new RedshiftHandler($config);

            case 'prometheus':
                return new PrometheusHandler($config);

            /** CSV Handlers */
            case 'web-csv':
                return new WebCsvHandler($config);

            case 'file-csv':
                return new FileCsvHandler($config);

            case 'stdout-csv':
                return new StdoutCsvHandler($config);

            /** Miscellaneous Handlers */
            case 'stdout':
                return new StdoutHandler($config);

            /** Unsupported Type */
            default:
                throw new InvalidArgumentException("Unsupported type: $type");
        }
    }
}
