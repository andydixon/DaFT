<?php
namespace Federaliser\Dataformats;
/**
 * Class HandlerFactory
 * Returns an instance of the appropriate handler based on the configuration.
 */
class HandlerFactory
{
    /**
     * Create a handler instance based on the 'type' in the config.
     *
     * @param array $config
     * @return DataFormatHandlerInterface
     * @throws \InvalidArgumentException
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

            /** Strange ones */
            case 'stdout':
                return new StdoutHandler($config);
                
            default:
                throw new \InvalidArgumentException("Unsupported type: $type");
        }
    }
}