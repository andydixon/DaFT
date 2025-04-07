<?php

namespace DaFT;

use PDOException;
use DateTime;
use DaFT\LogAlerting;

class Logger
{
    // Log PDOException with the query and parameters.
    public static function logPDOException(PDOException $exception, string $query = '', array $params = [])
    {
        // Ensure the 'logs' directory exists, or create it
        self::ensureLogDirectoryExists();

        // Get the current date for filename suffix (YYYYMMDD format)
        $date = new DateTime();
        $logFileName = $_SERVER['DOCUMENT_ROOT'].'/../logs/PDOExceptionLog.' . $date->format('Ymd');

        // Prepare the log message
        $logMessage = "[" . $date->format('Y-m-d H:i:s') . "] " .
            "PDOException: " . $exception->getMessage() . "\n" .
            "File: " . $exception->getFile() . "\n" .
            "Line: " . $exception->getLine() . "\n" .
            "Query: " . ($query ?: 'N/A') . "\n" .
            "Params: " . self::formatParams($params) . "\n\n";
        // LogAlerting::sendAlert($logMessage);

        // Write to the log file
        file_put_contents($logFileName, $logMessage, FILE_APPEND);
    }

    // Log generic messages with an identifier and alert level.
    public static function logGeneric(string $identifier, string $alertLevel, string $message)
    {
        // Ensure the 'logs' directory exists, or create it
        self::ensureLogDirectoryExists();

        // Get the current date for filename suffix (YYYYMMDD format)
        $date = new DateTime();

        $logFileName = $_SERVER['DOCUMENT_ROOT'].'/../logs/' . $identifier . 'Log.' . $date->format('Ymd');

        // Prepare the log message
        $logMessage = "[" . $date->format('Y-m-d H:i:s') . "] " .
            strtoupper($alertLevel) . " - " . $message ;
        LogAlerting::sendAlert($logMessage);
        // Write to the log file
        file_put_contents($logFileName, $logMessage. "\n---\n\n", FILE_APPEND);
    }

    // Helper function to format the parameters array
    private static function formatParams(array $params)
    {
        if (empty($params)) {
            return 'N/A';
        }

        $formattedParams = [];
        foreach ($params as $key => $value) {
            // Handle potential array or object values
            $formattedParams[] = "$key: " . (is_array($value) || is_object($value) ? json_encode($value) : $value);
        }

        return implode(', ', $formattedParams);
    }

    // Ensure the 'logs' directory exists or create it with proper permissions
    private static function ensureLogDirectoryExists()
    {
        $logDirectory = $_SERVER['DOCUMENT_ROOT'].'/../logs';

        // Check if the logs directory exists
        if (!is_dir($logDirectory)) {
            // Try to create the directory with appropriate permissions
            if (!mkdir($logDirectory, 0777, true)) {
                throw new \RuntimeException("Failed to create log directory: $logDirectory");
            }
        }

        // Ensure the directory is writable
        if (!is_writable($logDirectory)) {
            throw new \RuntimeException("Log directory is not writable: $logDirectory");
        }
    }
}
