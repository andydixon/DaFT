<?php
namespace Federaliser\Dataformats;

use PDO;
use PDOException;

/**
 * Class MysqlHandler
 *
 * Executes queries against a MySQL database using PDO.
 */
class MysqlHandler extends AbstractHandler
{
    public function handle(): array
    {
        $host   = $this->params['source'] ?? '';
        $port   = $this->params['port'] ?? 3306;
        $dbName = $this->params['default_db'] ?? '';
        $user   = $this->params['username'] ?? '';
        $pass   = $this->params['password'] ?? '';
        $sql    = $this->params['query'] ?? '';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            return ['error' => $ex->getMessage(), 'query' => $sql];
        }
    }
}