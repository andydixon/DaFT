<?php
namespace Federaliser\Dataformats;

use PDO;
use PDOException;

/**
 * Class MysqlHandler
 * 
 * Executes queries against a MySQL database using PDO.
 * - This class is designed to handle MySQL database connections and query execution.
 * - It supports secure database interactions using prepared statements to prevent SQL injection.
 * - It inherits common functionality from `AbstractHandler`, such as data normalization and filtering.
 * 
 * Security Notice:
 * - To prevent SQL injection, always use parameterised queries or prepared statements.
 * - This class safely binds query parameters using PDO's built-in functionality.
 * 
 * Usage Example:
 * ```
 * $config = [
 *     'type' => 'mysql',
 *     'source' => 'localhost',
 *     'port' => 3306,
 *     'default_db' => 'example_db',
 *     'username' => 'root',
 *     'password' => 'password',
 *     'query' => 'SELECT * FROM users WHERE status = :status',
 *     'params' => [
 *         'status' => 'active'
 *     ]
 * ];
 * 
 * $handler = new MysqlHandler($config);
 * $result = $handler->handle();
 * ```
 * 
 * Example Output:
 * ```
 * [
 *     ['id' => 1, 'name' => 'Alice', 'email' => 'alice@example.com'],
 *     ['id' => 2, 'name' => 'Bob', 'email' => 'bob@example.com']
 * ]
 * ```
 * 
 * Design Considerations:
 * - This class promotes secure database access using prepared statements.
 * - It handles database errors gracefully and provides meaningful error messages for debugging.
 * 
 * @author Andy Dixon
 * @created 2025-01-16
 * @namespace Federaliser\Dataformats
 */
class MysqlHandler extends AbstractHandler
{
    /**
     * Handles executing the configured query against a MySQL database.
     * 
     * This method:
     * - Establishes a PDO connection using the `mysql` driver.
     * - Uses prepared statements to securely execute the query.
     * - Fetches the results as an associative array.
     * - Normalises the array structure and optionally filters the data by query keys.
     * - Handles and logs database connection and query errors gracefully.
     * 
     * Example:
     * ```
     * $config = [
     *     'type' => 'mysql',
     *     'source' => 'localhost',
     *     'port' => 3306,
     *     'default_db' => 'example_db',
     *     'username' => 'root',
     *     'password' => 'password',
     *     'query' => 'SELECT * FROM users WHERE status = :status',
     *     'params' => [
     *         'status' => 'active'
     *     ]
     * ];
     * 
     * $handler = new MysqlHandler($config);
     * $result = $handler->handle();
     * ```
     * 
     * Error Handling:
     * - Throws `RuntimeException` if the database connection fails.
     * - Catches `PDOException` and returns an error message with the query.
     * - Uses `PDO::ERRMODE_EXCEPTION` to catch and handle database errors gracefully.
     * 
     * Security Note:
     * - Uses prepared statements with bound parameters to prevent SQL injection attacks.
     * - Ensure that user input is sanitised before passing it as query parameters.
     * 
     * @return array Query results as an associative array or error details.
     * 
     * @throws \RuntimeException If the database connection fails.
     */
    public function handle(): array
    {
        // Retrieve connection details from configuration
        $host   = $this->config['source'] ?? '';
        $port   = $this->config['port'] ?? 3306;
        $dbName = $this->config['default_db'] ?? '';
        $user   = $this->config['username'] ?? '';
        $pass   = $this->config['password'] ?? '';
        $sql    = $this->config['query'] ?? '';
        $params = $this->config['params'] ?? [];

        try {
            // Build the Data Source Name (DSN) for the MySQL driver
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            
            // Establish a PDO connection with error handling mode
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Prepare and execute the query using prepared statements
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Fetch all results as an associative array
            $data = $stmt->fetchAll();

            // Normalise the array structure
            $data = $this->normaliseArray($data);

            // Filter the data if query keys are specified
            return $this->filterData($data);

        } catch (PDOException $ex) {
            // Return error details for debugging (consider logging in production)
            return [
                'error' => $ex->getMessage(),
                'query' => $sql,
                'params' => $params
            ];
        }
    }
}
