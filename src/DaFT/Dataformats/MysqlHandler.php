<?php
namespace DaFT\Dataformats;

use PDO;
use PDOException;
use DaFT\Logger;

class MysqlHandler extends GenericHandler
{
    protected $lockfileDir = '/tmp/mysql_replication_locks'; // Directory to store lockfiles

    public function handle(): array
    {
        // Retrieve connection details from configuration
        $host   = $this->config['source'] ?? '';
        $port   = $this->config['port'] ?? 3306;
        $dbName = $this->config['default_db'] ?? '';
        $user   = $this->config['username'] ?? '';
        $pass   = $this->config['password'] ?? '';
        $sql    = parent::parseSQL($this->config['query'] ?? '');
        $params = $this->config['params'] ?? [];
        $replicaCheck = $this->config['checkReplication'] ?? false;
        $alertingTolerance = (int)($this->config['alertTolerance'] ?? 1);
        $identifier = $this->config['identifier'] ?? 'default';
        $silenced = false;
        $newLock = false;

        try {
            // Build the Data Source Name (DSN) for the MySQL driver
            $dsn = "mysql:host={$host};port={$port};dbname={$dbName};charset=utf8mb4";

            // Establish a PDO connection with error handling mode
            $pdo = new PDO($dsn, $user, $pass, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);

            // Check replication status first
            if ($replicaCheck) {
                $replicationStatus = $pdo->query("SHOW SLAVE STATUS")->fetch();

                // Ensure lockfile directory exists
                if (!is_dir($this->lockfileDir)) {
                    mkdir($this->lockfileDir, 0755, true);
                }

                $lockfile = "{$this->lockfileDir}/replication_alert_{$host}.lock";

                // Replication lag check logic
                if ($replicationStatus && isset($replicationStatus['Seconds_Behind_Master'])) {
                    $lag = (int)$replicationStatus['Seconds_Behind_Master'];

                    if ($lag >= 60) {
                        $silenced = true;
                        // If lockfile doesn't exist or is older than 15 mins, create/update it and log
                        if (!file_exists($lockfile) || (time() - filemtime($lockfile)) > 900) {
                            touch($lockfile);
                            $newLock = true;
                            Logger::logGeneric("replicationLagAlert", "ERROR", "Replication lag on {$host}: {$lag} seconds behind master. Data may be inconsistent.");
                        }
                    } else {
                        // If replication has caught up, remove lockfile and log
                        if (file_exists($lockfile)) {
                            unlink($lockfile);
                            Logger::logGeneric("replicationRecovered", "INFO", "Replication on {$host} has caught up ({$lag} seconds behind master). Lockfile removed.");
                        }
                    }
                }
            } // End of replica checking code

            // Prepare and execute the main query using prepared statements
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);

            // Fetch all results as an associative array
            $data = $stmt->fetchAll();

            // Alerting tolerance logic
            $toleranceLockfile = "{$this->lockfileDir}/zero_records_{$identifier}.lock";

            if (count($data) == 0) {
                $failureCount = file_exists($toleranceLockfile) ? (int)file_get_contents($toleranceLockfile) : 0;
                $failureCount++;

                if ($failureCount > $alertingTolerance) {
                    Logger::logGeneric("zeroRecordsReturned", "WARN", "Zero data returned for identifier '{$identifier}' after {$failureCount} attempts.");
                    if (file_exists($toleranceLockfile)) {
                        unlink($toleranceLockfile);
                    }
                } else {
                    file_put_contents($toleranceLockfile, $failureCount);
                }
            } else {
                // Remove tolerance lockfile if data is now available
                if (file_exists($toleranceLockfile)) {
                    unlink($toleranceLockfile);
                }
            }

            // Normalise the array structure
            $data = $this->normaliseArray($data);

            // Filter the data if query keys are specified
            return $this->filterData($data);

        } catch (PDOException $ex) {
            Logger::logPDOException($ex, $sql, $params);
            return [
                'error' => $ex->getMessage(),
                'query' => $sql,
                'params' => $this->config
            ];
        }
    }
}
