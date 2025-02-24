<?php
namespace Federaliser\Dataformats;

use PDO;
use PDOException;

/**
 * Class MssqlHandler
 *
 * Executes queries against a Microsoft SQL Server database using PDO with the sqlsrv driver.
 */
class MssqlHandler extends AbstractHandler
{
    public function handle(): array
    {
        $host   = $this->params['source'] ?? '';
        $port   = $this->params['port'] ?? 1433;
        $dbName = $this->params['default_db'] ?? '';
        $user   = $this->params['username'] ?? '';
        $pass   = $this->params['password'] ?? '';
        $sql    = $this->params['query'] ?? '';

        try {
            $dsn = "sqlsrv:Server={$host},{$port};Database={$dbName}";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            return ['error' => $ex->getMessage(), 'query' => $sql];
        }
    }
}