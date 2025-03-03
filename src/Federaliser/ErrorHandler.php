<?php
namespace Federaliser;

class ErrorHandler
{

    public static function handleError($errno, $errstr, $errfile, $errline): void
    {
        $response = [
            "status" => "CORE_ERROR",
            "message" => $errstr,
            "debug" => [
                "file" => $errfile,
                "line" => $errline
            ]
        ];

        header('HTTP/1.1 500 Internal Server Error');
        header('Content-Type: application/json');

        $jsonOutput = json_encode($response);

        if ($jsonOutput === false) {
            $jsonOutput = json_encode([
                "status" => "CORE_ERROR",
                "message" => "JSON encoding failed for error response"
            ]);
        }

        echo $jsonOutput;

        // Use the injected terminator
        if (!self::isRunningUnderPHPUnit()) {
            exit;
        } else {
            echo "(I self identify as terminating here)";
        }
    }

    private static function isRunningUnderPHPUnit(): bool
{
    foreach (debug_backtrace() as $trace) {
        if (isset($trace['class']) && strpos($trace['class'], 'PHPUnit\\') === 0) {
            return true;
        }
    }
    return false;
}
}
