<?php
/**
 * Federaliser ConnectionFactory
 * Centeralises all database specific functionality together. New DB engines can be added to this file. Or not. Whevs.
 *
 * @author Andy Dixon <andy@andydixon.com>
 * @created 2025-01-16
 */

namespace Federaliser;

use PDO;
use PDOException;

/**
 * Factory to create a connection and run the query
 * depending on 'type': mysql, mssql, redshift, or prometheus, etc.
 */
class ConnectionFactory
{
    private array $params;

    public function __construct(array $params)
    {
        $this->params = $params;
    }

    /**
     * Run the query and return results. For simplicity,
     * we are using basic PDO for MySQL, MSSQL, and Redshift.
     * For Prometheus, we demonstrate a simple cURL call to /api/v1/query.
     */
    public function runQuery(): array
    {
        $type = $this->params['type'] ?? '';
        switch (strtolower($type)) {
            case 'mysql':
                return $this->runMysqlQuery();
            case 'mssql':
                return $this->runMssqlQuery();
            case 'redshift':
                return $this->runRedshiftQuery();
            case 'prometheus':
                return $this->runPrometheusQuery();
            default:
                return [
                    'error' => "Unknown data source type: {$type}"
                ];
        }
    }

    private function runMysqlQuery(): array
    {
        $host = $this->params['hostname'] ?? '';
        $port = $this->params['port'] ?? 3306;
        $dbName = $this->params['default_db'] ?? '';
        $user = $this->params['username'] ?? '';
        $pass = $this->params['password'] ?? '';
        $sql = $this->params['query'] ?? '';

        try {
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            return ['error' => $ex->getMessage(), "query" => $sql];
        }
    }

    private function runMssqlQuery(): array
    {
        $host = $this->params['hostname'] ?? '';
        $port = $this->params['port'] ?? 1433;
        $dbName = $this->params['default_db'] ?? '';
        $user = $this->params['username'] ?? '';
        $pass = $this->params['password'] ?? '';
        $sql = $this->params['query'] ?? '';

        try {
            // DSN format for SQL Server via sqlsrv driver
            $dsn = "sqlsrv:Server={$host},{$port};Database={$dbName}";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            return ['error' => $ex->getMessage(), "query" => $sql];
        }
    }

    private function runRedshiftQuery(): array
    {
        $host = $this->params['hostname'] ?? '';
        $port = $this->params['port'] ?? 5439;
        $dbName = $this->params['default_db'] ?? '';
        $user = $this->params['username'] ?? '';
        $pass = $this->params['password'] ?? '';
        $sql = $this->params['query'] ?? '';

        try {
            // Redshift is PostgreSQL-compatible
            $dsn = "pgsql:host={$host};port={$port};dbname={$dbName}";
            $pdo = new PDO($dsn, $user, $pass, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
            $stmt = $pdo->query($sql);
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (PDOException $ex) {
            return ['error' => $ex->getMessage(), "query" => $sql];
        }
    }

    private function runPrometheusQuery(): array
    {
        $host = rtrim($this->params['hostname'] ?? '', '/');
        $port = $this->params['port'] ?? 9090;
        $query = $this->params['query'] ?? 'up';  // 'sql' field is our Prometheus metric
        $url = "{$host}:{$port}/api/v1/query?query=" . urlencode($query);

        $ch = curl_init($url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            $error = curl_error($ch);
            curl_close($ch);
            return ['error' => $error];
        }

        $statusCode = curl_getinfo($ch, CURLINFO_RESPONSE_CODE);
        curl_close($ch);

        if ($statusCode !== 200) {
            return ['error' => "Prometheus query failed with HTTP status: $statusCode", "query" => $query];
        }

        $json = json_decode($response, true);
        return $json ?? ['error' => 'Invalid JSON response from Prometheus', "query" => $query];
    }
}
