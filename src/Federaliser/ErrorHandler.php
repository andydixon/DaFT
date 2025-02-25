<?php
namespace Federaliser;

class ErrorHandler
{
    private $terminator;

    public function __construct(Terminator $terminator)
    {
        $this->terminator = $terminator;
    }

    public function handleError($errno, $errstr, $errfile, $errline): void
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
        $this->terminator->terminate();
    }
}
