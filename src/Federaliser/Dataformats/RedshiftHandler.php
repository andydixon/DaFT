<?php
namespace Federaliser\Dataformats;

use PDO;
use PDOException;

/**
 * Class RedshiftHandler
 *
 * Executes queries against an Amazon Redshift cluster (using the PostgreSQL PDO driver).
 */
class RedshiftHandler extends AbstractHandler
{
    public function handle(): array
    {
        $host   = $this->params['hostname'] ?? '';
        $port   = $this->params['port'] ?? 5439;
        $dbName = $this->params['default_db'] ?? '';
        $user   = $this->params['username'] ?? '';
        $pass   = $this->params['password'] ?? '';
        $sql    = $this->params['query'] ?? '';

        try {
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            return ['error' => $ex->getMessage(), 'query' => $sql];
        }
    }
}
