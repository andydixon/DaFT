<?php
namespace DaFT\Dataformats;

use PDO;
use PDOException;

/**
 * Class AbstractQueryHandler
 * 
 * Provides common functionality for all database query handlers.
 * This class was primarily used by database-driven data sources but is now deprecated.
 * 
 * ⚠️ DEPRECATED:
 * This class is deprecated and should no longer be used for new implementations.
 * Consider using the unified `AbstractHandler` class or other relevant alternatives.
 * 
 * Reason for Deprecation:
 * - This class was tied to legacy database structures and does not support newer data formats.
 * - It was silly to have two different abstract classes for the \DaFT\Dataformats namespace.
 * 
 * Suggested Alternative:
 * - Use `AbstractHandler` for newer database interactions.
 * 
 * @deprecated This class is deprecated and will be removed in future releases.
 *             Consider using `DatabaseQueryHandler` or other modern alternatives.
 * 
 * Usage Example:
 * ```
 * // ⚠️ DEPRECATED - For reference only
 * class MyQueryHandler extends AbstractQueryHandler {
 *     public function execute() {
 *         // Custom query execution logic
 *     }
 * }
 * 
 * $handler = new MyQueryHandler([
 *     'source' => 'database',
 *     'port' => 3306,
 *     'default_db' => 'example_db',
 *     'username' => 'root',
 *     'password' => 'password',
 *     'query' => 'SELECT * FROM users'
 * ]);
 * 
 * $handler->execute();
 * ```
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace DaFT\Dataformats
 * @package DaFT\Dataformats
 */
abstract class AbstractQueryHandler implements DataFormatHandlerInterface
{
    /**
     * @var array Configuration parameters for the query.
     * 
     * Expected keys:
     * - `source`: The database source type (e.g., mysql, mssql, redshift).
     * - `port`: The port number for database connection.
     * - `default_db`: The default database name.
     * - `username`: The username for database authentication.
     * - `password`: The password for database authentication.
     * - `query`: The SQL query to be executed.
     */
    protected array $params;

    /**
     * Constructor.
     * 
     * Initialises the query handler with the provided configuration parameters.
     * 
     * Example:
     * ```
     * $params = [
     *     'source' => 'mysql',
     *     'port' => 3306,
     *     'default_db' => 'example_db',
     *     'username' => 'root',
     *     'password' => 'password',
     *     'query' => 'SELECT * FROM users'
     * ];
     * 
     * $handler = new MyQueryHandler($params);
     * ```
     * 
     * @param array $params Array containing keys such as source, port, default_db, username, password, query, etc.
     */
    public function __construct(array $params)
    {
        $this->params = $params;
    }
}
