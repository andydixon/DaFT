<?php
namespace DaFT\Dataformats;

use DaFT\Logger;
use RuntimeException;

/**
 * Class AthenaHandler
 * 
 * Executes queries against an AWS Athena database using the ODBC driver.
 * - Reads AWS credentials from config and sets environment variables for ODBC driver.
 * - Inherits common functionality from `GenericHandler`, such as data normalisation and filtering.
 * 
 * Security Notice:
 * - Athena ODBC requires environment variables for AWS authentication.
 * - This class automatically sets AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY from config.
 * - Ensure AWS credentials are stored securely.
 * 
 * @namespace DaFT\Dataformats
 */
class AthenaHandler extends GenericHandler
{
    /**
     * Handles executing the configured query against AWS Athena.
     *
     * @return array Query results as an associative array or error details.
     * 
     * @throws \RuntimeException If the database connection fails.
     */
    public function handle(): array
    {
        $catalog   = $this->config['source'] ?? 'AwsDataCatalog';
        $region    = $this->config['region'] ?? 'eu-west-2';
        $workgroup = $this->config['workgroup'] ?? 'primary';
        $output    = $this->config['output'] ?? 's3://your-output-bucket/';
        $sql       = parent::parseSQL($this->config['query'] ?? '');
        $params    = $this->config['params'] ?? [];

        // Set AWS credentials as environment variables if provided in config
        if (!empty($this->config['aws_access_key_id']) && !empty($this->config['aws_secret_access_key'])) {
            putenv('AWS_ACCESS_KEY_ID=' . $this->config['aws_access_key_id']);
            putenv('AWS_SECRET_ACCESS_KEY=' . $this->config['aws_secret_access_key']);
        }

        // Build ODBC connection string for Athena
        $connStr = sprintf(
            "Driver=Amazon Athena ODBC;AwsRegion=%s;S3OutputLocation=%s;Workgroup=%s;Catalog=%s;",
            $region,
            $output,
            $workgroup,
            $catalog
        );

        try {
            $conn = odbc_connect($connStr, '', '');
            if (!$conn) {
                throw new RuntimeException('Failed to connect to Athena ODBC: ' . odbc_errormsg());
            }

            // Prepare the query (manual interpolation as ODBC doesn't support bound parameters)
            if (!empty($params)) {
                foreach ($params as $param) {
                    $safeParam = is_numeric($param) ? $param : "'" . str_replace("'", "''", $param) . "'";
                    $sql = preg_replace('/\?/', $safeParam, $sql, 1);
                }
            }

            $result = odbc_exec($conn, $sql);
            if (!$result) {
                throw new RuntimeException('Query execution failed: ' . odbc_errormsg($conn));
            }

            // Fetch results
            $data = [];
            while ($row = odbc_fetch_array($result)) {
                $data[] = $row;
            }

            if (count($data) === 0) {
                Logger::logGeneric("zeroRecordsReturned", "WARN", "Zero data returned for identifier '" . ($this->config['identifier'] ?? '') . "'");
            }

            odbc_close($conn);

            // Normalise and filter
            $data = $this->normaliseArray($data);
            return $this->filterData($data);

        } catch (\Throwable $ex) {
            Logger::logGeneric('athenaHandlerError', 'ERROR', $ex->getMessage());
            return [
                'error' => $ex->getMessage(),
                'query' => $sql,
                'params' => $this->config
            ];
        }
    }
}
